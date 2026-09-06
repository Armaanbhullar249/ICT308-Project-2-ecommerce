<?php
require_once __DIR__.'/helpers.php';$data=input();$action=$data['action']??'list';
try{
if($action==='list')respond(['ok'=>true,'products'=>product_rows()]);
require_role(['admin']);
if($action==='clear'){db()->exec('DELETE FROM products');db()->exec('DELETE FROM categories');foreach(glob(dirname(__DIR__).'/uploads/products/meta_*.json')?:[] as $f)@unlink($f);respond(['ok'=>true,'products'=>[],'categories'=>[],'rules'=>[]]);}
if($action==='create' || $action==='update'){
    $name=trim((string)($data['name']??''));if(!$name)fail('Name is required.');
    $cat=trim((string)($data['category']??''));$categoryId=null;if($cat!==''){$st=db()->prepare('SELECT id FROM categories WHERE name=? LIMIT 1');$st->execute([$cat]);$categoryId=$st->fetchColumn()?:null;}
    $image=save_data_url($data['image']??'');
    if($action==='create'){
      $st=db()->prepare('INSERT INTO products(category_id,name,brand,price,stock,subtitle,description,image,is_active) VALUES(?,?,?,?,?,?,?,?,1)');
      $st->execute([$categoryId,$name,trim((string)($data['brand']??'')),(float)($data['price']??0),max(0,(int)($data['stock']??0)),trim((string)($data['subtitle']??'')),trim((string)($data['description']??'')),$image]);$id=(int)db()->lastInsertId();
    }else{
      $id=(int)($data['id']??0);if(!$id)fail('Product id is required.');
      if($image==='' && array_key_exists('image',$data)) $image=''; elseif($image==='' && !array_key_exists('image',$data)){ $st=db()->prepare('SELECT image FROM products WHERE id=?');$st->execute([$id]);$image=(string)($st->fetchColumn()?:''); }
      $st=db()->prepare('UPDATE products SET category_id=?,name=?,brand=?,price=?,stock=?,subtitle=?,description=?,image=? WHERE id=?');
      $st->execute([$categoryId,$name,trim((string)($data['brand']??'')),(float)($data['price']??0),max(0,(int)($data['stock']??0)),trim((string)($data['subtitle']??'')),trim((string)($data['description']??'')),$image,$id]);
      $st=db()->prepare('DELETE FROM product_specs WHERE product_id=?');$st->execute([$id]);
    }
    $specs=$data['specs']??[];if(is_array($specs)){$st=db()->prepare('INSERT INTO product_specs(product_id,specification) VALUES(?,?)');foreach($specs as $s){$s=trim((string)$s);if($s!=='')$st->execute([$id,$s]);}}
    save_product_meta($id,$data);
    respond(['ok'=>true,'products'=>product_rows(),'productId'=>(string)$id]);
}
if($action==='delete'){$id=(int)($data['id']??0);if(!$id)fail('Product id required.');$st=db()->prepare('DELETE FROM products WHERE id=?');$st->execute([$id]);@unlink(dirname(__DIR__).'/uploads/products/meta_'.$id.'.json');respond(['ok'=>true,'products'=>product_rows()]);}
fail('Unknown action.');
}catch(Throwable $e){fail($e->getMessage(),500);}
