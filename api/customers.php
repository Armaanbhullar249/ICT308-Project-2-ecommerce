<?php
require_once __DIR__.'/helpers.php';
$data=input();$action=$data['action']??'';
try{
if($action==='status'){
    require_role(['owner']);
    $username=trim((string)($data['username']??''));$disabled=(bool)($data['disabled']??false);
    $st=db()->prepare("UPDATE users SET status=? WHERE username=? AND role='customer'");$st->execute([$disabled?'inactive':'active',$username]);respond(['ok'=>true]);
}
if($action==='promoteOwner'){
    require_role(['admin']);
    $username=strtolower(trim((string)($data['username']??''))); if(!$username) fail('Enter a customer username.');
    $st=db()->prepare("UPDATE users SET role='owner',status='active' WHERE username=? AND role='customer'");$st->execute([$username]);
    if($st->rowCount()<1) fail('Customer account not found or is already staff.',404);
    respond(['ok'=>true]);
}
fail('Unknown action.');
}catch(Throwable $e){fail($e->getMessage(),500);}
