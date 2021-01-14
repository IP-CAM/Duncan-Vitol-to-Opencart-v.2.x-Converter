<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload and converter</title>
</head>
<body>

<form enctype="multipart/form-data" method="post">
    <input type="file" name="file_xml">
    <button type="submit">send!</button>
</form>

<?php
/* 
todo:
крок 1: завантажити
крок 2: чек файла
крок 3: кнопка категорій
крок 4: кнопка виробників
крок 5: кнопка товарів
крок 6: видалення файлу і вигрузка
*/

$message = '';

if(isset($_FILES[file_xml])){
    if($_FILES[file_xml][type] === 'text/xml'){

        $name = $_FILES[file_xml][name];
        $folder = $_FILES[file_xml][tmp_name];

        if(is_uploaded_file($folder)){
            // move_uploaded_file($folder, "./upload/$name");
            if(move_uploaded_file($folder, "./upload/$name")){
                // debug
                echo 'file ' . $name . ' uploaded!' . '<br><br>';
            }
        }

        if (file_exists("./upload/$name")) {

            // debug
            echo 'file' . $name . ' is exists <br><br>';

            $xml = simplexml_load_file("./upload/$name");

            $categories = $xml->categories->category;

            $time = time();
            $dump = '';
            foreach($categories as $category){
                $dump .=  "INSERT INTO `oc_category` VALUES ($category[id],'',0,0,0,0,1,$time,$time);";
                $cat = mb_strtoupper($category);
                $dump .=  "INSERT INTO `oc_category_description` VALUES ($category[id],1,'$cat','','','','','');";
            }
            // echo $dump, '<hr>';

            // goods
            $items = $xml->items->item;

            foreach($items as $item){
                // echo $item->categoryId, '<br>';
                echo $item->vendor, '<br>';
            }

/* 
            INSERT INTO `oc_product` VALUES (
                28,
                'Товар 1',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                939, -- кількість
                7,
                'catalog/demo/htc_touch_hd_1.jpg',
                5,
                1,
                100.0000,
                200,9,
                '2009-02-03',
                146.40,
                2,
                0.00,
                0.00,
                0.00,
                1,
                1,
                1,
                0,
                1,
                0,
                '2009-02-03 16:06:50',
                '2011-09-30 01:05:39');
 */
/* 
            CREATE TABLE `oc_product` (
                `product_id` int(11) NOT NULL AUTO_INCREMENT,
                `model` varchar(64) NOT NULL,
                `sku` varchar(64) NOT NULL,
                `upc` varchar(12) NOT NULL,
                `ean` varchar(14) NOT NULL,
                `jan` varchar(13) NOT NULL,
                `isbn` varchar(17) NOT NULL,
                `mpn` varchar(64) NOT NULL,
                `location` varchar(128) NOT NULL,
                `quantity` int(4) NOT NULL DEFAULT '0',
                `stock_status_id` int(11) NOT NULL,
                `image` varchar(255) DEFAULT NULL,
                `manufacturer_id` int(11) NOT NULL,
                `shipping` tinyint(1) NOT NULL DEFAULT '1',
                `price` decimal(15,4) NOT NULL DEFAULT '0.0000',
                `points` int(8) NOT NULL DEFAULT '0',
                `tax_class_id` int(11) NOT NULL,
                `date_available` date NOT NULL DEFAULT '0000-00-00',
                `weight` decimal(15,2) NOT NULL DEFAULT '0.00',
                `weight_class_id` int(11) NOT NULL DEFAULT '0',
                `length` decimal(15,2) NOT NULL DEFAULT '0.00',
                `width` decimal(15,2) NOT NULL DEFAULT '0.00',
                `height` decimal(15,2) NOT NULL DEFAULT '0.00',
                `length_class_id` int(11) NOT NULL DEFAULT '0',
                `subtract` tinyint(1) NOT NULL DEFAULT '1',
                `minimum` int(11) NOT NULL DEFAULT '1',
                `sort_order` int(11) NOT NULL DEFAULT '0',
                `status` tinyint(1) NOT NULL DEFAULT '0',
                `viewed` int(5) NOT NULL DEFAULT '0',
                `date_added` datetime NOT NULL,
                `date_modified` datetime NOT NULL,
                PRIMARY KEY (`product_id`)
              ) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8;
 */










        } else {
            exit('Не вдалося відкрити файл' . $name);
        }

    }
}
?>
<p>Пам'ять: 
<?=memory_get_usage()?>
</p>
</body>
</html>