<?php
require_once __DIR__.'/helpers.php';$u=require_login();$data=input();$action=$data['action']??'get';$uid=(int)$u['id'];
try{
$cid=active_cart_id($uid,true);
if($action==='get')respond(['ok'=>true,'cart'=>cart_shape($uid)]);
$pid=(int)($data['productId']??0);if(!$pid)fail('Product is required.');$st=db()->prepare('SELECT stock FROM products WHERE id=? AND is_active=1');$st->execute([$pid]);$stock=(int)($st->fetchColumn()?:0);if($stock<1)fail('Product is out of stock.');
if($action==='add'){$qty=max(1,(int)($data['qty']??1));$st=db()->prepare('SELECT quantity FROM cart_items WHERE cart_id=? AND product_id=?');$st->execute([$cid,$pid]);$cur=(int)($st->fetchColumn()?:0);$new=min($stock,$cur+$qty);$st=db()->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=VALUES(quantity)');$st->execute([$cid,$pid,$new]);}
elseif($action==='set'){$qty=max(0,(int)($data['qty']??0));if($qty===0){$st=db()->prepare('DELETE FROM cart_items WHERE cart_id=? AND product_id=?');$st->execute([$cid,$pid]);}else{$qty=min($qty,$stock);$st=db()->prepare('INSERT INTO cart_items(cart_id,product_id,quantity) VALUES(?,?,?) ON DUPLICATE KEY UPDATE quantity=VALUES(quantity)');$st->execute([$cid,$pid,$qty]);}}
elseif($action==='remove'){$st=db()->prepare('DELETE FROM cart_items WHERE cart_id=? AND product_id=?');$st->execute([$cid,$pid]);}
else fail('Unknown action.');respond(['ok'=>true,'cart'=>cart_shape($uid)]);
}catch(Throwable $e){fail($e->getMessage(),500);}
