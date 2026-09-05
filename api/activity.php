<?php
require_once __DIR__.'/helpers.php';$u=current_user();$data=input();$action=$data['action']??'';
try{
if(!$u)respond(['ok'=>true]);
if($action==='view'){$pid=(int)($data['productId']??0);if($pid){$st=db()->prepare("INSERT INTO product_activity(user_id,product_id,activity_type) VALUES(?,?,'view')");$st->execute([$u['id'],$pid]);}}
elseif($action==='search'){$q=trim((string)($data['query']??''));if(strlen($q)>=2){$st=db()->prepare('INSERT INTO search_history(user_id,search_term) VALUES(?,?)');$st->execute([$u['id'],$q]);}}
elseif($action==='removeSearch'){$q=trim((string)($data['query']??''));$st=db()->prepare('DELETE FROM search_history WHERE user_id=? AND LOWER(search_term)=LOWER(?)');$st->execute([$u['id'],$q]);}
elseif($action==='clearSearch'){$st=db()->prepare('DELETE FROM search_history WHERE user_id=?');$st->execute([$u['id']]);}
respond(['ok'=>true]);
}catch(Throwable $e){fail($e->getMessage(),500);}
