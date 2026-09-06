<?php
require_once __DIR__.'/helpers.php';$data=input();$action=$data['action']??'list';
try{
if($action==='list')respond(['ok'=>true,'rules'=>rule_rows()]);require_role(['admin']);
if($action==='add'){$name=trim((string)($data['name']??''));$when=(int)($data['whenId']??0);$then=(int)($data['thenId']??0);$trigger=$data['triggerAction']??'view';if(!in_array($trigger,['view','cart','purchase'],true))$trigger='view';if(!$name||!$when||!$then||$when===$then)fail('Choose two different products and a rule name.');$st=db()->prepare('INSERT INTO recommendation_rules(rule_name,trigger_action,trigger_product_id,recommended_product_id,is_active) VALUES(?,?,?,?,1)');$st->execute([$name,$trigger,$when,$then]);respond(['ok'=>true,'rules'=>rule_rows()]);}
if($action==='delete'){$id=(int)($data['id']??0);if(!$id)fail('Rule id required.');$st=db()->prepare('DELETE FROM recommendation_rules WHERE id=?');$st->execute([$id]);respond(['ok'=>true,'rules'=>rule_rows()]);}
fail('Unknown action.');
}catch(Throwable $e){fail($e->getMessage(),500);}
