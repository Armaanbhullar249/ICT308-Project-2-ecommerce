<?php
require_once __DIR__.'/helpers.php';$u=require_login();$data=input();
try{
if($_SERVER['REQUEST_METHOD']==='GET')respond(['ok'=>true,'profile'=>profile_shape($u)]);
$map=['firstName'=>'first_name','lastName'=>'last_name','email'=>'email','phone'=>'phone','address'=>'address','city'=>'city','zip'=>'postal_code','country'=>'country'];$sets=[];$vals=[];
foreach($map as $k=>$col){if(array_key_exists($k,$data)){$sets[]="$col=?";$v=trim((string)$data[$k]);$vals[]=(($k==='email' && $v==='')?null:$v);}}
if(array_key_exists('name',$data)){ $parts=preg_split('/\s+/',trim((string)$data['name']));$sets[]='first_name=?';$vals[]=array_shift($parts)?:$u['username'];$sets[]='last_name=?';$vals[]=implode(' ',$parts); }
if(!$sets)respond(['ok'=>true,'profile'=>profile_shape($u)]);$vals[]=$u['id'];$st=db()->prepare('UPDATE users SET '.implode(',',$sets).' WHERE id=?');$st->execute($vals);$fresh=current_user();respond(['ok'=>true,'profile'=>profile_shape($fresh),'user'=>session_shape($fresh)]);
}catch(PDOException $e){ if($e->getCode()==='23000')fail('That email address is already in use.',409); fail($e->getMessage(),500); }
