<?php
require_once __DIR__ . '/config/database.php';
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
header('Content-Type: application/json; charset=utf-8');

function input(): array {
    $raw = file_get_contents('php://input');
    if (!$raw) return $_POST ?: [];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : ($_POST ?: []);
}
function respond(array $data, int $status = 200): never {
    http_response_code($status);
    echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}
function fail(string $message, int $status = 400): never { respond(['ok'=>false,'error'=>$message], $status); }
function user_id(): ?int { return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null; }
function current_user(): ?array {
    if (!user_id()) return null;
    $st = db()->prepare('SELECT id,username,role,first_name,last_name,email,phone,address,city,postal_code,country,status,created_at FROM users WHERE id=? LIMIT 1');
    $st->execute([user_id()]);
    return $st->fetch() ?: null;
}
function require_login(): array { $u=current_user(); if(!$u) fail('Login required.',401); if($u['status']!=='active') fail('Account is inactive.',403); return $u; }
function require_role(array $roles): array { $u=require_login(); if(!in_array($u['role'],$roles,true)) fail('Access denied.',403); return $u; }
function display_name(array $u): string { $n=trim(($u['first_name']??'').' '.($u['last_name']??'')); return $n ?: $u['username']; }
function session_shape(?array $u): ?array {
    if(!$u) return null; $name=display_name($u);
    return ['id'=>(int)$u['id'],'username'=>$u['username'],'name'=>$name,'role'=>$u['role'],'initial'=>strtoupper(substr($name,0,1)),'label'=>$name];
}
function profile_shape(array $u): array {
    return [
        'id'=>(int)$u['id'],'username'=>$u['username'],'name'=>display_name($u),
        'firstName'=>$u['first_name']??'','lastName'=>$u['last_name']??'','email'=>$u['email']??'',
        'phone'=>$u['phone']??'','address'=>$u['address']??'','city'=>$u['city']??'',
        'zip'=>$u['postal_code']??'','country'=>$u['country']??'','role'=>$u['role'],
        'disabled'=>($u['status']??'active')!=='active','createdAt'=>strtotime($u['created_at']??'now')*1000
    ];
}

function product_meta(int $id): array {
    $file=dirname(__DIR__).'/uploads/products/meta_'.$id.'.json';
    if(!is_file($file)) return [];
    $data=json_decode((string)file_get_contents($file),true);
    return is_array($data)?$data:[];
}
function save_product_meta(int $id, array $data): void {
    $dir=dirname(__DIR__).'/uploads/products'; if(!is_dir($dir))mkdir($dir,0775,true);
    $gallery=[];
    foreach(($data['gallery']??[]) as $src){
        $saved=save_data_url((string)$src); if($saved!=='')$gallery[]=$saved;
    }
    $meta=['weight'=>(float)($data['weight']??0.5),'gallery'=>array_values(array_unique($gallery))];
    file_put_contents($dir.'/meta_'.$id.'.json',json_encode($meta,JSON_UNESCAPED_SLASHES));
}

function product_rows(): array {
    $rows=db()->query('SELECT p.*, c.name category FROM products p LEFT JOIN categories c ON c.id=p.category_id WHERE p.is_active=1 ORDER BY p.id DESC')->fetchAll();
    if(!$rows) return [];
    $spec=db()->query('SELECT product_id,specification FROM product_specs ORDER BY id')->fetchAll();
    $tags=db()->query('SELECT product_id,tag FROM product_tags ORDER BY id')->fetchAll();
    $specs=[];$tagMap=[];
    foreach($spec as $r) $specs[$r['product_id']][]=$r['specification'];
    foreach($tags as $r) $tagMap[$r['product_id']][]=$r['tag'];
    return array_map(function($r)use($specs,$tagMap){
        $id=(string)$r['id']; $meta=product_meta((int)$r['id']);
        return ['id'=>$id,'name'=>$r['name'],'category'=>$r['category']??'Other','brand'=>$r['brand']??'',
            'price'=>(float)$r['price'],'stock'=>(int)$r['stock'],'weight'=>(float)($meta['weight']??0.5),
            'subtitle'=>$r['subtitle']?:($r['category']??''),'description'=>$r['description']??'',
            'image'=>$r['image']??'','gallery'=>$meta['gallery']??[],'tone'=>'blue','emoji'=>'📦',
            'tags'=>$tagMap[$r['id']]??[],'specs'=>$specs[$r['id']]??[]];
    },$rows);
}
function category_names(): array { return array_column(db()->query('SELECT name FROM categories ORDER BY name')->fetchAll(),'name'); }
function rule_rows(): array {
    $rows=db()->query('SELECT rr.*, tp.name trigger_name, rp.name recommended_name FROM recommendation_rules rr JOIN products tp ON tp.id=rr.trigger_product_id JOIN products rp ON rp.id=rr.recommended_product_id WHERE rr.is_active=1 ORDER BY rr.id DESC')->fetchAll();
    return array_map(fn($r)=>['id'=>(int)$r['id'],'name'=>$r['rule_name'],'when'=>ucfirst($r['trigger_action']).' '.$r['trigger_name'],'then'=>'Recommend '.$r['recommended_name'],'whenId'=>(string)$r['trigger_product_id'],'thenId'=>(string)$r['recommended_product_id'],'triggerAction'=>$r['trigger_action']],$rows);
}
function active_cart_id(int $uid, bool $create=true): ?int {
    $st=db()->prepare("SELECT id FROM carts WHERE user_id=? AND status='active' ORDER BY id DESC LIMIT 1");$st->execute([$uid]);$id=$st->fetchColumn();
    if($id) return (int)$id; if(!$create) return null;
    $st=db()->prepare("INSERT INTO carts(user_id,status) VALUES(?,'active')");$st->execute([$uid]);return (int)db()->lastInsertId();
}
function cart_shape(int $uid): array {
    $cid=active_cart_id($uid,false); if(!$cid) return [];
    $st=db()->prepare('SELECT product_id,quantity FROM cart_items WHERE cart_id=? ORDER BY id');$st->execute([$cid]);
    return array_map(fn($r)=>['id'=>(string)$r['product_id'],'qty'=>(int)$r['quantity']],$st->fetchAll());
}
function order_rows(?int $uid=null): array {
    $sql='SELECT o.*, u.username, u.first_name, u.last_name FROM orders o JOIN users u ON u.id=o.user_id';$args=[];
    if($uid!==null){$sql.=' WHERE o.user_id=?';$args[]=$uid;} $sql.=' ORDER BY o.id DESC';
    $st=db()->prepare($sql);$st->execute($args);$orders=$st->fetchAll(); if(!$orders)return[];
    $ids=array_column($orders,'id');$ph=implode(',',array_fill(0,count($ids),'?'));
    $it=db()->prepare("SELECT oi.*, COALESCE(c.name,'Other') category_name FROM order_items oi LEFT JOIN products p ON p.id=oi.product_id LEFT JOIN categories c ON c.id=p.category_id WHERE oi.order_id IN ($ph) ORDER BY oi.id");$it->execute($ids);$items=[];
    foreach($it->fetchAll() as $r)$items[$r['order_id']][]=['id'=>(string)($r['product_id']??''),'name'=>$r['product_name'],'category'=>$r['category_name']??'Other','qty'=>(int)$r['quantity'],'price'=>(float)$r['unit_price'],'lineTotal'=>(float)$r['line_total']];
    return array_map(function($o)use($items){$oi=$items[$o['id']]??[];return ['dbId'=>(int)$o['id'],'id'=>$o['order_number'],'placedAt'=>strtotime($o['placed_at']??$o['created_at']??'now')*1000,'items'=>$oi,'itemCount'=>array_sum(array_column($oi,'qty')),'subtotal'=>(float)$o['subtotal'],'discount'=>(float)$o['discount'],'shippingCost'=>(float)$o['shipping_cost'],'total'=>(float)$o['total'],'customer'=>trim(($o['first_name']??'').' '.($o['last_name']??''))?:$o['username'],'customerUsername'=>$o['username'],'trackingNumber'=>$o['tracking_number']??'','shippingMethod'=>$o['shipping_method']??'standard','paymentMethod'=>$o['payment_method']??'card','status'=>$o['status']];},$orders);
}
function customer_settings(int $uid): array {
    $st=db()->prepare('SELECT * FROM customer_settings WHERE user_id=? LIMIT 1');$st->execute([$uid]);$r=$st->fetch()?:[];
    return ['notifications'=>['orders'=>isset($r['order_notifications'])?(bool)$r['order_notifications']:true,'deals'=>isset($r['deal_notifications'])?(bool)$r['deal_notifications']:true,'recommendations'=>isset($r['recommendation_notifications'])?(bool)$r['recommendation_notifications']:true],
      'communications'=>['email'=>isset($r['email_communication'])?(bool)$r['email_communication']:true,'sms'=>isset($r['sms_communication'])?(bool)$r['sms_communication']:false,'marketing'=>isset($r['marketing_communication'])?(bool)$r['marketing_communication']:false],
      'privacy'=>['personalization'=>isset($r['personalization'])?(bool)$r['personalization']:true,'analytics'=>isset($r['analytics'])?(bool)$r['analytics']:true],
      'appearance'=>$r['appearance']??'system'];
}
function save_data_url(?string $value): string {
    $value=(string)$value; if($value==='' || !str_starts_with($value,'data:image/')) return $value;
    if(!preg_match('#^data:image/(png|jpeg|jpg|webp);base64,(.+)$#',$value,$m)) return '';
    $bin=base64_decode($m[2],true); if($bin===false || strlen($bin)>950000) return '';
    $ext=$m[1]==='jpeg'?'jpg':$m[1]; $name='product_'.bin2hex(random_bytes(8)).'.'.$ext;
    $dir=dirname(__DIR__).'/uploads/products'; if(!is_dir($dir))mkdir($dir,0775,true); file_put_contents($dir.'/'.$name,$bin);
    return 'uploads/products/'.$name;
}
