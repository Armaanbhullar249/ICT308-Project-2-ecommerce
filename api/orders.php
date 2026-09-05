<?php
require_once __DIR__.'/helpers.php';$u=require_login();$data=input();$action=$data['action']??'list';
try{
if($action==='list'){$orders=in_array($u['role'],['admin','owner'],true)?order_rows(null):order_rows((int)$u['id']);respond(['ok'=>true,'orders'=>$orders]);}
if($action==='clear'){require_role(['admin','owner']);db()->exec('DELETE FROM orders');respond(['ok'=>true,'orders'=>[]]);}
if($action!=='create')fail('Unknown action.');
$uid=(int)$u['id'];$cid=active_cart_id($uid,false);if(!$cid)fail('Cart is empty.');
$pdo=db();$pdo->beginTransaction();
$st=$pdo->prepare('SELECT ci.product_id,ci.quantity,p.name,p.price,p.stock,c.name category FROM cart_items ci JOIN products p ON p.id=ci.product_id LEFT JOIN categories c ON c.id=p.category_id WHERE ci.cart_id=? FOR UPDATE');$st->execute([$cid]);$items=$st->fetchAll();if(!$items){$pdo->rollBack();fail('Cart is empty.');}
$subtotal=0;foreach($items as $i){if((int)$i['quantity']>(int)$i['stock']){$pdo->rollBack();fail($i['name'].' does not have enough stock.');}$subtotal+=(float)$i['price']*(int)$i['quantity'];}
$discount=$subtotal>1000?25:0;$shippingCost=max(0,(float)($data['shippingCost']??0));$total=max(0,$subtotal-$discount+$shippingCost);
$orderNumber='WE-'.date('ymdHis').'-'.random_int(10,99);$tracking='TRK-'.str_replace('WE-','',$orderNumber);$shippingMethod=substr((string)($data['shippingMethod']??'standard'),0,100);$paymentMethod=substr((string)($data['paymentMethod']??'card'),0,100);
$st=$pdo->prepare("INSERT INTO orders(order_number,user_id,status,subtotal,discount,shipping_cost,total,shipping_method,payment_method,tracking_number) VALUES(?,?,'processing',?,?,?,?,?,?,?)");$st->execute([$orderNumber,$uid,$subtotal,$discount,$shippingCost,$total,$shippingMethod,$paymentMethod,$tracking]);$oid=(int)$pdo->lastInsertId();
$oi=$pdo->prepare('INSERT INTO order_items(order_id,product_id,product_name,quantity,unit_price,line_total) VALUES(?,?,?,?,?,?)');
$stock=$pdo->prepare('UPDATE products SET stock=stock-? WHERE id=?');$act=$pdo->prepare("INSERT INTO product_activity(user_id,product_id,activity_type) VALUES(?,?,'purchase')");
foreach($items as $i){$line=(float)$i['price']*(int)$i['quantity'];$oi->execute([$oid,$i['product_id'],$i['name'],$i['quantity'],$i['price'],$line]);$stock->execute([$i['quantity'],$i['product_id']]);$act->execute([$uid,$i['product_id']]);}
$addr=$pdo->prepare('INSERT INTO order_addresses(order_id,address_type,first_name,last_name,email,phone,address,city,postal_code,country) VALUES(?,?,?,?,?,?,?,?,?,?)');
$billing=is_array($data['billing']??null)?$data['billing']:[];$shipping=is_array($data['shippingAddress']??null)?$data['shippingAddress']:[];
$norm=function($a,$fallback=[])use($u){return ['firstName'=>$a['firstName']??$fallback['firstName']??$u['first_name']??'','lastName'=>$a['lastName']??$fallback['lastName']??$u['last_name']??'','email'=>$a['email']??$fallback['email']??$u['email']??'','phone'=>$a['phone']??$fallback['phone']??$u['phone']??'','address'=>$a['address']??$fallback['address']??$u['address']??'','city'=>$a['city']??$fallback['city']??$u['city']??'','zip'=>$a['zip']??$fallback['zip']??$u['postal_code']??'','country'=>$a['country']??$fallback['country']??$u['country']??''];};
$b=$norm($billing);$s=$norm($shipping,$b);if(!$b['address']||!$b['city']||!$b['country']){$pdo->rollBack();fail('Billing address is incomplete.');}if(!$s['address']||!$s['city']||!$s['country']){$pdo->rollBack();fail('Shipping address is incomplete.');}
$addr->execute([$oid,'billing',$b['firstName'],$b['lastName'],$b['email'],$b['phone'],$b['address'],$b['city'],$b['zip'],$b['country']]);$addr->execute([$oid,'shipping',$s['firstName'],$s['lastName'],$s['email'],$s['phone'],$s['address'],$s['city'],$s['zip'],$s['country']]);
$st=$pdo->prepare("UPDATE carts SET status='completed' WHERE id=?");$st->execute([$cid]);$pdo->commit();$orders=order_rows($uid);respond(['ok'=>true,'order'=>$orders[0]??null,'orders'=>$orders,'cart'=>[],'products'=>product_rows()]);
}catch(Throwable $e){if(isset($pdo)&&$pdo->inTransaction())$pdo->rollBack();fail($e->getMessage(),500);}
