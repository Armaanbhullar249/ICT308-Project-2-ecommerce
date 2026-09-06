<?php
require_once __DIR__.'/helpers.php';$data=input();$action=$data['action']??'list';
try{
if($action==='list')respond(['ok'=>true,'categories'=>category_names()]);require_role(['admin']);
if($action==='add'){$name=trim((string)($data['name']??''));if(!$name)fail('Category name is required.');$st=db()->prepare('INSERT INTO categories(name) VALUES(?)');$st->execute([$name]);respond(['ok'=>true,'categories'=>category_names()]);}
if($action==='delete'){$name=trim((string)($data['name']??''));$st=db()->prepare('DELETE FROM categories WHERE name=?');$st->execute([$name]);respond(['ok'=>true,'categories'=>category_names()]);}
fail('Unknown action.');
}catch(PDOException $e){if($e->getCode()==='23000')fail('Category already exists.',409);fail($e->getMessage(),500);}
