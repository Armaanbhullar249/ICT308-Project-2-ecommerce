<?php
require_once dirname(__DIR__).'/api/config/database.php';

$root = dirname(__DIR__);
$photoDir = $root.'/uploads/products';

function photos_for(string $prefix): array {
    global $photoDir;
    $files = glob($photoDir.'/'.$prefix.'-*.jpg') ?: [];
    sort($files);
    return array_map(fn($f) => 'uploads/products/'.basename($f), $files);
}

function pick(array $list, int $i): string {
    return $list[$i % count($list)];
}

$pdo = db();
$pdo->beginTransaction();

$wantedCats = ['Laptops','Phones','Audio','Accessories','Tablets','Wearables','TVs','Cameras','Gaming','Smart Home'];
$insCat = $pdo->prepare('INSERT IGNORE INTO categories(name) VALUES(?)');
foreach ($wantedCats as $c) $insCat->execute([$c]);

$catIds = [];
foreach ($pdo->query('SELECT id,name FROM categories') as $row) $catIds[$row['name']] = (int)$row['id'];

$byCat = [
    'Laptops' => photos_for('laptop'),
    'Phones' => photos_for('phone'),
    'Audio' => photos_for('audio'),
    'Accessories' => photos_for('acc'),
    'Tablets' => photos_for('tablet'),
    'Wearables' => photos_for('watch'),
    'TVs' => photos_for('tv'),
    'Cameras' => photos_for('cam'),
    'Gaming' => photos_for('game'),
    'Smart Home' => photos_for('home'),
];
foreach ($byCat as $cat => $files) {
    if (!$files) throw new RuntimeException("No photos for {$cat}. Run scripts/download-product-photos.sh first.");
}

$products = [
    // Keep original 8 names so existing recommendation rules still match
    ['Laptops','AeroBook 14','Warner',1299,12,'Everyday laptop','A light 14-inch laptop for study and work.',['14-inch display','16GB RAM','512GB SSD'],['laptop','study']],
    ['Laptops','StudioPro 16','Warner',2199,6,'Creator laptop','High-performance 16-inch laptop for media work.',['16-inch display','32GB RAM','1TB SSD'],['laptop','pro']],
    ['Phones','Pulse Phone','Warner',899,20,'Flagship phone','A fast phone with a long-lasting battery.',['6.7-inch display','128GB storage'],['phone']],
    ['Phones','Pulse Mini','Warner',649,18,'Compact phone','A smaller phone that still handles daily apps well.',['6.1-inch display','128GB storage'],['phone']],
    ['Audio','QuietBuds','Warner',179,40,'Wireless earbuds','Noise-reducing earbuds for commute and focus.',['Active noise reduction','24-hour battery case'],['audio']],
    ['Audio','Hall Speaker','Warner',249,15,'Bluetooth speaker','Portable speaker with a full room sound.',['360° sound','12-hour battery'],['audio']],
    ['Accessories','ChargeHub','Warner',59,50,'USB-C charger','A compact 65W charger for laptops and phones.',['65W USB-C','Foldable plug'],['charger']],
    ['Accessories','Shield Case','Warner',29,80,'Phone case','Protective case for Pulse Phone.',['Drop protection','Soft lining'],['case']],

    ['Laptops','AeroBook Air 13','Warner',1099,14,'Ultralight laptop','Travel laptop with all-day battery.',['13.3-inch display','8GB RAM','256GB SSD'],['laptop','travel']],
    ['Laptops','AeroBook Flex 14','Warner',1399,10,'2-in-1 laptop','Laptop that folds into a tablet.',['Touch screen','16GB RAM','512GB SSD'],['laptop','2-in-1']],
    ['Laptops','StudioPro 14','Warner',1899,8,'Compact creator laptop','Portable workstation for editing.',['14-inch OLED','32GB RAM','1TB SSD'],['laptop','pro']],
    ['Laptops','StudioPro 18','Warner',2799,4,'Desktop replacement','Large screen for design and video.',['18-inch display','64GB RAM','2TB SSD'],['laptop','pro']],
    ['Laptops','CampusBook 15','Nova',799,22,'Student laptop','Reliable laptop for lectures and assignments.',['15.6-inch display','8GB RAM','256GB SSD'],['laptop','study']],
    ['Laptops','CampusBook Plus','Nova',949,16,'Student plus','More storage for labs and media files.',['15.6-inch display','16GB RAM','512GB SSD'],['laptop','study']],
    ['Laptops','Nimbus Slim 13','Nimbus',1199,11,'Slim metal laptop','Thin aluminium body with a quiet keyboard.',['13-inch display','16GB RAM','512GB SSD'],['laptop']],
    ['Laptops','Nimbus 15 OLED','Nimbus',1599,9,'Colour laptop','OLED panel for photos and film.',['15-inch OLED','16GB RAM','1TB SSD'],['laptop']],
    ['Laptops','PeakWork 16','Peak',1699,7,'Business laptop','Long battery and a bright outdoor screen.',['16-inch display','16GB RAM','512GB SSD'],['laptop','work']],
    ['Laptops','PeakWork 14 LTE','Peak',1549,9,'Connected laptop','Laptop with mobile data for fieldwork.',['14-inch display','16GB RAM','512GB SSD','LTE'],['laptop','work']],
    ['Laptops','Volt Gaming 15','Volt',1999,6,'Gaming laptop','High refresh screen for games and streaming.',['15.6-inch 165Hz','32GB RAM','1TB SSD'],['laptop','gaming']],
    ['Laptops','Volt Gaming 17','Volt',2399,5,'Big-screen gaming','Desktop-class graphics in a 17-inch chassis.',['17.3-inch 240Hz','32GB RAM','1TB SSD'],['laptop','gaming']],
    ['Laptops','Orbit Chromebook','Orbit',449,25,'Web laptop','Fast boot for browsing and documents.',['14-inch display','8GB RAM','128GB storage'],['laptop']],
    ['Laptops','Lumen Book 16','Lumen',1290,12,'Family laptop','Simple laptop for home and homework.',['16-inch display','8GB RAM','512GB SSD'],['laptop']],

    ['Phones','Pulse Pro Max','Warner',1199,15,'Large flagship','Biggest Pulse screen and the best cameras.',['6.9-inch display','256GB storage','Triple camera'],['phone']],
    ['Phones','Pulse SE','Warner',499,28,'Everyday phone','Solid battery and a clean camera for daily use.',['6.1-inch display','128GB storage'],['phone']],
    ['Phones','Nova Fold','Nova',1699,5,'Foldable phone','Phone that opens into a small tablet.',['7.6-inch fold','256GB storage'],['phone','fold']],
    ['Phones','Nova Flip','Nova',999,8,'Flip phone','Compact flip design with a cover screen.',['6.7-inch inner display','128GB storage'],['phone','flip']],
    ['Phones','Apex One','Apex',799,18,'Fast 5G phone','Smooth display and reliable 5G.',['6.6-inch 120Hz','128GB storage'],['phone']],
    ['Phones','Apex One Pro','Apex',949,12,'Camera phone','Night mode and optical zoom.',['6.7-inch display','256GB storage'],['phone']],
    ['Phones','Orbit Go','Orbit',329,30,'Budget phone','A dependable phone without extra cost.',['6.5-inch display','64GB storage'],['phone']],
    ['Phones','Orbit Go 5G','Orbit',399,24,'Budget 5G','5G on a smaller budget.',['6.5-inch display','128GB storage'],['phone']],
    ['Phones','Lumen Note','Lumen',699,14,'Stylus phone','Built-in stylus for notes and sketches.',['6.8-inch display','128GB storage','Stylus'],['phone']],
    ['Phones','Peak Rugged','Peak',549,16,'Tough phone','Drop and splash resistant for outdoor work.',['6.4-inch display','128GB storage'],['phone']],
    ['Phones','Volt Play','Volt',599,20,'Gaming phone','High refresh rate and cooling vents.',['6.7-inch 144Hz','256GB storage'],['phone','gaming']],
    ['Phones','Nimbus Air Phone','Nimbus',749,13,'Light phone','Slim body with a sharp selfie camera.',['6.2-inch display','128GB storage'],['phone']],
    ['Phones','Warner Desk Phone','Warner',219,10,'Home phone set','Speakerphone for the kitchen or office.',['Speakerphone','Caller ID'],['phone']],

    ['Audio','QuietBuds Pro','Warner',249,32,'Premium earbuds','Stronger ANC and wireless charging.',['ANC','Wireless case'],['audio']],
    ['Audio','QuietBuds Sport','Warner',159,26,'Sport earbuds','Sweat resistant with ear hooks.',['IPX5','8-hour buds'],['audio']],
    ['Audio','Studio Cans','Warner',329,12,'Studio headphones','Wired monitoring headphones for mixing.',['Wired','50mm drivers'],['audio']],
    ['Audio','Travel Cans','Nova',199,18,'Travel headphones','Fold-flat ANC headphones for flights.',['ANC','30-hour battery'],['audio']],
    ['Audio','Kids Cans','Lumen',49,40,'Kids headphones','Volume-limited headphones for children.',['Volume limit','Soft pads'],['audio']],
    ['Audio','Desk Speaker Duo','Apex',189,14,'Desktop speakers','USB speakers for a study desk.',['USB powered','Bluetooth'],['audio']],
    ['Audio','Party Boom','Volt',179,16,'Party speaker','Loud portable speaker with lights.',['RGB lights','20-hour battery'],['audio']],
    ['Audio','Shelf Speaker','Nimbus',399,8,'Home speaker','Bookshelf speaker for vinyl and TV.',['RCA input','Wooden cabinet'],['audio']],
    ['Audio','Soundbar 2.1','Peak',349,11,'TV soundbar','Soundbar with a wireless subwoofer.',['2.1 channels','HDMI ARC'],['audio']],
    ['Audio','Neckband Buds','Orbit',79,35,'Neckband earphones','Secure fit for commuting.',['12-hour battery','Magnetic buds'],['audio']],
    ['Audio','DJ Mix Mini','Volt',229,9,'DJ controller','Compact mixer for bedroom sets.',['2 decks','USB'],['audio']],
    ['Audio','Podcast Mic Kit','Apex',129,22,'USB microphone','Microphone and stand for calls and podcasts.',['USB-C','Mute button'],['audio']],

    ['Accessories','ChargeHub 100','Warner',79,40,'100W charger','Charges a laptop and phone together.',['100W','2 USB-C ports'],['charger']],
    ['Accessories','Mag Cable Pack','Warner',24,70,'Cable pack','Three USB-C cables in one pack.',['1m cables','Braided'],['cable']],
    ['Accessories','Desk Mouse Pro','Nova',49,45,'Wireless mouse','Quiet clicks for lectures and offices.',['Silent click','USB receiver'],['mouse']],
    ['Accessories','Ergo Keyboard','Nova',89,20,'Ergo keyboard','Split layout that reduces wrist strain.',['Split keys','USB-C'],['keyboard']],
    ['Accessories','Mech Keys 75','Volt',129,18,'Mechanical keyboard','Compact 75% board with hot-swap switches.',['Hot-swap','RGB'],['keyboard']],
    ['Accessories','Wide Mousepad','Volt',29,50,'Desk mat','Large cloth mat for keyboard and mouse.',['900mm wide','Stitched edge'],['desk']],
    ['Accessories','USB Hub 7','Apex',39,36,'USB hub','Seven ports for a crowded desk.',['7 ports','USB-C host'],['hub']],
    ['Accessories','Laptop Sleeve 14','Nimbus',35,28,'Laptop sleeve','Padded sleeve for 14-inch laptops.',['Neoprene','14-inch'],['sleeve']],
    ['Accessories','Laptop Sleeve 16','Nimbus',39,24,'Laptop sleeve','Padded sleeve for 16-inch laptops.',['Neoprene','16-inch'],['sleeve']],
    ['Accessories','Screen Clean Kit','Lumen',12,90,'Cleaner kit','Spray and cloth for phones and laptops.',['Alcohol-free','Microfibre'],['care']],
    ['Accessories','Power Bank 20K','Peak',59,33,'Power bank','Charge a phone several times on the go.',['20000mAh','USB-C PD'],['battery']],
    ['Accessories','Car Charge Duo','Orbit',22,48,'Car charger','Two ports for the car.',['30W','Dual USB-C'],['charger']],
    ['Accessories','Webcam Cover Pack','Lumen',9,100,'Privacy covers','Slider covers for laptop cameras.',['3 pack','Slim'],['privacy']],
    ['Accessories','SSD 1TB Pocket','Apex',109,19,'Portable SSD','Fast pocket drive for video files.',['1TB','USB-C 10Gbps'],['storage']],
    ['Accessories','Monitor Arm','Peak',79,15,'Monitor mount','Gas-spring arm for a 27-inch screen.',['VESA','Clamp'],['desk']],

    ['Tablets','SketchPad 11','Warner',649,14,'Drawing tablet','Stylus included for notes and art.',['11-inch','Stylus'],['tablet']],
    ['Tablets','SketchPad 12.9','Warner',999,8,'Pro tablet','Larger canvas for illustration.',['12.9-inch','120Hz','Stylus'],['tablet']],
    ['Tablets','FamilyTab 10','Lumen',279,22,'Family tablet','Shared tablet for streaming and homework.',['10.1-inch','64GB'],['tablet']],
    ['Tablets','FamilyTab Kids','Lumen',229,18,'Kids tablet','Case and time limits for younger users.',['8-inch','Parental controls'],['tablet']],
    ['Tablets','Nova Tab S','Nova',749,10,'Slim tablet','Thin Android tablet with stereo speakers.',['11-inch','128GB'],['tablet']],
    ['Tablets','Nova Tab Ultra','Nova',899,7,'OLED tablet','High-contrast screen for comics and film.',['12.4-inch OLED','256GB'],['tablet']],
    ['Tablets','Orbit Tab','Orbit',199,26,'Web tablet','Simple tablet for email and browsing.',['10-inch','32GB'],['tablet']],
    ['Tablets','Peak Field Tab','Peak',549,9,'Rugged tablet','Works with gloves on a worksite.',['10-inch','IP65'],['tablet']],
    ['Tablets','Apex Note Tab','Apex',429,13,'Note tablet','Keyboard folio sold separately.',['10.5-inch','128GB'],['tablet']],
    ['Tablets','Nimbus Mini Tab','Nimbus',349,16,'Mini tablet','One-hand tablet for reading.',['8.3-inch','64GB'],['tablet']],
    ['Tablets','Warner Tab Keyboard','Warner',129,20,'Tablet keyboard','Detachable keyboard for SketchPad 11.',['Backlit','Trackpad'],['tablet','keyboard']],
    ['Tablets','Warner Tab Folio','Warner',59,30,'Tablet case','Stand case with pencil holder.',['Sleep/wake','Pencil loop'],['tablet','case']],

    ['Wearables','Pulse Watch','Warner',399,22,'Smartwatch','Fitness tracking and phone alerts.',['GPS','5-day battery'],['watch']],
    ['Wearables','Pulse Watch SE','Warner',249,28,'Everyday watch','Sleep tracking without extra sport modes.',['Heart rate','7-day battery'],['watch']],
    ['Wearables','Peak Trail Watch','Peak',329,14,'Outdoor watch','Maps and long battery for hikes.',['Offline maps','20-day battery'],['watch']],
    ['Wearables','Volt Beat Band','Volt',79,40,'Fitness band','Light band for steps and workouts.',['AMOLED','14-day battery'],['band']],
    ['Wearables','Nova Ring','Nova',299,12,'Health ring','Sleep and recovery on a small ring.',['Size kit','Wireless case'],['ring']],
    ['Wearables','Kids Watch','Lumen',99,18,'Kids watch','Calls and location for families.',['GPS','School mode'],['watch']],
    ['Wearables','Nimbus Classic','Nimbus',219,15,'Hybrid watch','Analogue face with smart alerts.',['Always-on hands','7-day battery'],['watch']],
    ['Wearables','Apex Swim Watch','Apex',189,17,'Swim watch','Tracks pool and open-water sessions.',['5 ATM','Stroke detect'],['watch']],
    ['Wearables','Orbit Glasses Lite','Orbit',179,8,'Smart glasses','Open-ear audio for walking.',['Open-ear','Mic'],['glasses']],
    ['Wearables','Warner Watch Bands','Warner',35,50,'Watch strap pack','Sport and leather straps for Pulse Watch.',['3 pack','22mm'],['watch']],
    ['Wearables','Charger Dock Watch','Warner',29,34,'Watch dock','Night stand charger for Pulse Watch.',['Magnetic','USB-C'],['watch']],
    ['Wearables','Pulse Watch Ultra','Warner',649,9,'Adventure watch','Brighter display and extra battery.',['Titanium','10 ATM'],['watch']],

    ['TVs','Cinema 55 4K','Warner',899,10,'55-inch 4K TV','Bright 4K TV for movies and sport.',['55-inch','4K HDR'],['tv']],
    ['TVs','Cinema 65 OLED','Warner',1699,6,'65-inch OLED','Deep blacks for film nights.',['65-inch OLED','120Hz'],['tv']],
    ['TVs','Cinema 75 4K','Warner',1499,5,'75-inch 4K TV','Big living-room screen.',['75-inch','4K HDR'],['tv']],
    ['TVs','Nova Frame 55','Nova',1199,7,'Art TV','Gallery mode when the TV is off.',['55-inch','Art mode'],['tv']],
    ['TVs','Lumen 43 HD','Lumen',379,14,'Bedroom TV','Compact TV for a smaller room.',['43-inch','Full HD'],['tv']],
    ['TVs','Lumen 50 4K','Lumen',499,12,'Value 4K TV','4K without extra cost.',['50-inch','4K'],['tv']],
    ['TVs','Peak Outdoor 43','Peak',899,4,'Outdoor TV','Bright panel for a covered patio.',['43-inch','High brightness'],['tv']],
    ['TVs','Apex QLED 65','Apex',1299,6,'QLED TV','Colour volume for daytime viewing.',['65-inch QLED','120Hz'],['tv']],
    ['TVs','Orbit Stick 4K','Orbit',69,40,'Streaming stick','Plug into HDMI for apps.',['4K','Voice remote'],['tv']],
    ['TVs','Warner Wall Mount','Warner',49,22,'TV mount','Tilt mount for 43–75 inch TVs.',['Tilt','VESA'],['tv']],

    ['Cameras','Shot 24 Kit','Warner',899,9,'Mirrorless kit','Camera and 24-70 lens for beginners.',['24MP','4K video'],['camera']],
    ['Cameras','Shot 40 Pro','Warner',1899,4,'Pro mirrorless','High resolution for print work.',['40MP','IBIS'],['camera']],
    ['Cameras','Pocket Vlog','Nova',449,16,'Vlog camera','Flip screen for talking-head video.',['1-inch sensor','4K'],['camera']],
    ['Cameras','Action Cam 5','Volt',329,18,'Action camera','Waterproof cam for bike and surf.',['5.3K','Waterproof'],['camera']],
    ['Cameras','Action Cam Mini','Volt',199,20,'Mini action cam','Clip-on camera for helmets.',['2.7K','Magnetic mount'],['camera']],
    ['Cameras','Instant Print','Lumen',119,24,'Instant camera','Prints photos in a minute.',['Film pack extra','Selfie mirror'],['camera']],
    ['Cameras','Webcam 1080','Apex',59,30,'1080p webcam','Clear camera for classes and meetings.',['1080p 60','Stereo mics'],['webcam']],
    ['Cameras','Webcam 4K','Apex',129,14,'4K webcam','Sharp picture for streaming.',['4K','HDR'],['webcam']],
    ['Cameras','Drone Air 2','Nimbus',799,6,'Camera drone','Folding drone with a 4K gimbal.',['4K gimbal','36 min flight'],['drone']],
    ['Cameras','Tripod Travel','Peak',79,21,'Travel tripod','Compact tripod for cameras and phones.',['Aluminium','Phone clamp'],['tripod']],
    ['Cameras','Lens 50mm','Warner',349,8,'Prime lens','Portrait lens for Shot cameras.',['50mm f/1.8','Auto focus'],['lens']],
    ['Cameras','SD 128 Pack','Orbit',29,55,'Memory cards','Two 128GB cards for photo days.',['128GB V30','2 pack'],['storage']],

    ['Gaming','PlayPad','Volt',69,35,'Controller','Wireless controller for PC and TV.',['Bluetooth','USB-C'],['gaming']],
    ['Gaming','PlayPad Pro','Volt',89,22,'Pro controller','Hall-effect sticks and extra paddles.',['Hall sticks','Paddles'],['gaming']],
    ['Gaming','Headset Cloud','Volt',119,18,'Gaming headset','Surround mix for games and chat.',['7.1 virtual','Detachable mic'],['gaming','audio']],
    ['Gaming','Chair SitPlay','Peak',249,7,'Gaming chair','Supportive chair for long sessions.',['Lumbar pillow','Recline'],['gaming']],
    ['Gaming','Capture Card','Apex',159,11,'Capture card','Record a console on a laptop.',['4K60 pass-through','USB-C'],['gaming']],
    ['Gaming','RGB Strip Desk','Volt',25,40,'Desk lights','Light strip that follows music and games.',['App control','USB'],['gaming']],
    ['Gaming','Fight Stick Mini','Nova',99,10,'Fight stick','Arcade stick for fighting games.',['Sanwa-style','USB'],['gaming']],
    ['Gaming','Racing Wheel','Nova',229,6,'Racing wheel','Wheel and pedals for sim racing.',['Force feedback','Pedal set'],['gaming']],
    ['Gaming','Stream Light Key','Lumen',89,13,'Key light','Even light for face-cam streams.',['Dimmable','USB-C'],['gaming']],
    ['Gaming','Console Stand Cool','Orbit',39,19,'Console stand','Stand with extra USB ports.',['Vertical','USB hub'],['gaming']],
    ['Gaming','Game Storage Tower','Lumen',45,16,'Game tower','Holds discs and cases on a shelf.',['24 slots','Wood'],['gaming']],
    ['Gaming','Volt Play Bundle','Volt',149,8,'Starter bundle','Controller, headset, and mousepad.',['3 items','PC/console'],['gaming']],

    ['Smart Home','Hub Mini','Warner',89,24,'Smart hub','Connects lights, plugs, and sensors.',['Matter','Wi-Fi 6'],['smarthome']],
    ['Smart Home','Voice Cylinder','Nova',99,20,'Smart speaker','Voice assistant in a small speaker.',['Far-field mics','Bluetooth'],['smarthome','audio']],
    ['Smart Home','Voice Display','Nova',149,12,'Smart display','Screen for recipes, calls, and cameras.',['8-inch','Camera shutter'],['smarthome']],
    ['Smart Home','Bulb Colour 4pk','Lumen',39,45,'Colour bulbs','Four colour bulbs for a room.',['RGB','Matter'],['smarthome']],
    ['Smart Home','Plug Twin','Orbit',29,50,'Smart plugs','Two plugs with energy stats.',['Energy monitor','App'],['smarthome']],
    ['Smart Home','Cam Indoor','Peak',69,18,'Indoor camera','Pet and room camera with night vision.',['2K','Privacy shutter'],['smarthome']],
    ['Smart Home','Cam Doorbell','Peak',179,10,'Video doorbell','See the door from your phone.',['1536p','Battery or wired'],['smarthome']],
    ['Smart Home','Lock Latch','Apex',219,7,'Smart lock','Keypad and phone unlock.',['PIN','Auto lock'],['smarthome']],
    ['Smart Home','Thermostat Air','Nimbus',169,9,'Smart thermostat','Schedules heating from an app.',['Room sensor','Matter'],['smarthome']],
    ['Smart Home','Leak Sensor 2pk','Orbit',35,28,'Leak sensors','Alerts if a pipe drips.',['2 pack','Battery'],['smarthome']],
    ['Smart Home','Blind Motor','Apex',129,8,'Blind motor','Opens blinds on a schedule.',['Quiet motor','App'],['smarthome']],
    ['Smart Home','Warner Sensor Kit','Warner',79,15,'Sensor kit','Door, motion, and temperature sensors.',['3 sensors','Hub ready'],['smarthome']],
];

if (count($products) < 100) {
    throw new RuntimeException('Catalogue definition has fewer than 100 products.');
}

$sel = $pdo->prepare('SELECT id FROM products WHERE name=? LIMIT 1');
$upd = $pdo->prepare('UPDATE products SET category_id=?,brand=?,price=?,stock=?,subtitle=?,description=?,image=?,is_active=1 WHERE id=?');
$ins = $pdo->prepare('INSERT INTO products(category_id,name,brand,price,stock,subtitle,description,image,is_active) VALUES(?,?,?,?,?,?,?,?,1)');
$delSpec = $pdo->prepare('DELETE FROM product_specs WHERE product_id=?');
$delTag = $pdo->prepare('DELETE FROM product_tags WHERE product_id=?');
$insSpec = $pdo->prepare('INSERT INTO product_specs(product_id,specification) VALUES(?,?)');
$insTag = $pdo->prepare('INSERT INTO product_tags(product_id,tag) VALUES(?,?)');

$index = [];
foreach ($products as $i => $row) {
    $index[$row[0]] = ($index[$row[0]] ?? -1) + 1;
    $cat = $row[0];
    $name = $row[1];
    $photos = $byCat[$cat];
    $image = pick($photos, $index[$cat]);
    $gallery = [];
    for ($g = 1; $g < min(3, count($photos)); $g++) {
        $gallery[] = pick($photos, $index[$cat] + $g);
    }

    $sel->execute([$name]);
    $id = $sel->fetchColumn();
    $payload = [$catIds[$cat], $row[2], $row[3], $row[4], $row[5], $row[6], $image];
    if ($id) {
        $upd->execute([...$payload, (int)$id]);
        $id = (int)$id;
    } else {
        $ins->execute([$catIds[$cat], $name, $row[2], $row[3], $row[4], $row[5], $row[6], $image]);
        $id = (int)$pdo->lastInsertId();
    }

    $delSpec->execute([$id]);
    $delTag->execute([$id]);
    foreach ($row[7] as $spec) $insSpec->execute([$id, $spec]);
    foreach ($row[8] as $tag) $insTag->execute([$id, $tag]);

    $meta = ['weight' => 0.45 + (($i % 6) * 0.08), 'gallery' => array_values(array_unique(array_filter($gallery, fn($g) => $g !== $image)))];
    file_put_contents($photoDir.'/meta_'.$id.'.json', json_encode($meta, JSON_UNESCAPED_SLASHES));
}

$pdo->commit();
$count = (int)$pdo->query('SELECT COUNT(*) FROM products WHERE is_active=1')->fetchColumn();
$withPhoto = (int)$pdo->query("SELECT COUNT(*) FROM products WHERE is_active=1 AND image <> ''")->fetchColumn();
echo "Active products: {$count}\nWith photos: {$withPhoto}\n";
