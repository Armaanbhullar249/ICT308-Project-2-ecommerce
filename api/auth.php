<?php
require_once __DIR__.'/helpers.php';
$data=input();$action=$data['action']??'';
try {
if($action==='register'){
    $username=strtolower(trim((string)($data['username']??'')));$password=(string)($data['password']??'');$name=trim((string)($data['name']??''));
    if(!$username||!$password)fail('Username and password are required.'); if(strlen($password)<4)fail('Password must be at least 4 characters.');
    if(!preg_match('/^[a-z0-9._-]{3,50}$/',$username))fail('Username must be 3-50 characters using letters, numbers, dot, dash or underscore.');
    $st=db()->prepare('SELECT id FROM users WHERE username=? LIMIT 1');$st->execute([$username]);if($st->fetch())fail('An account with that username already exists.',409);
    $parts=preg_split('/\s+/',$name?:$username);$first=array_shift($parts)?:$username;$last=implode(' ',$parts);
    $st=db()->prepare("INSERT INTO users(username,password_hash,role,first_name,last_name,status) VALUES(?,?,'customer',?,?, 'active')");$st->execute([$username,password_hash($password,PASSWORD_DEFAULT),$first,$last]);
    respond(['ok'=>true]);
}
if($action==='login'){
    $username=strtolower(trim((string)($data['username']??'')));$password=(string)($data['password']??'');
    $st=db()->prepare('SELECT * FROM users WHERE username=? LIMIT 1');$st->execute([$username]);$u=$st->fetch();
    if(!$u||$u['status']!=='active'||!password_verify($password,$u['password_hash']))fail('Invalid username or password.',401);
    session_regenerate_id(true);$_SESSION['user_id']=(int)$u['id'];
    $guest=$data['guestCart']??[];
    if(is_array($guest)&&$guest){$cid=active_cart_id((int)$u['id'],true);foreach($guest as $g){$pid=(int)($g['id']??0);$qty=max(1,(int)($g['qty']??1));if(!$pid)continue;$s=db()->prepare('SELECT stock FROM products WHERE id=? AND is_active=1');$s->execute([$pid]);$stock=(int)($s->fetchColumn()?:0);if($stock<1)continue;$qty=min($qty,$stock);$s=db()->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=LEAST(quantity+VALUES(quantity),?)');$s->execute([$cid,$pid,$qty,$stock]);}}
    $fresh=current_user();respond(['ok'=>true,'user'=>session_shape($fresh),'cart'=>cart_shape((int)$fresh['id'])]);
}
if($action==='logout'){$_SESSION=[];if(ini_get('session.use_cookies')){$p=session_get_cookie_params();setcookie(session_name(),'',time()-42000,$p['path'],$p['domain'],$p['secure'],$p['httponly']);}session_destroy();respond(['ok'=>true]);}
if($action==='password'){$u=require_login();$cur=(string)($data['currentPassword']??'');$new=(string)($data['newPassword']??'');if(strlen($new)<4)fail('Password must be at least 4 characters.');$st=db()->prepare('SELECT password_hash FROM users WHERE id=?');$st->execute([$u['id']]);if(!password_verify($cur,(string)$st->fetchColumn()))fail('Current password is incorrect.',400);$st=db()->prepare('UPDATE users SET password_hash=? WHERE id=?');$st->execute([password_hash($new,PASSWORD_DEFAULT),$u['id']]);respond(['ok'=>true]);}
fail('Unknown action.');
} catch(Throwable $e){fail($e->getMessage(),500);}
