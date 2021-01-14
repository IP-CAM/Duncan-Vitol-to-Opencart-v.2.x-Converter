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
// todo: видалення файлу через 10 хвилин

if(isset($_FILES[file_xml])){
    if($_FILES[file_xml][type] === 'text/xml'){

        // файл
        $name = $_FILES[file_xml][name];

        // папка
        $folder = $_FILES[file_xml][tmp_name];

        if(is_uploaded_file($folder)){
            move_uploaded_file($folder, "./upload/$name");
        }

        // якщо файл існує
        if (file_exists("./upload/$name")) {

            // видаляємо, якщо була підміна типу
            if(mime_content_type("./upload/$name") != 'text/xml'){
                unlink("./upload/$name");
                exit();
            }

            // основна змінна
            $xml = simplexml_load_file("./upload/$name");

            // products
            $items = $xml->items->item;

            // часова мітка
            $time = date('Y-m-d h:i:s', time());

            // основний файл дампу
            $dump = 'TRUNCATE TABLE oc_manufacturer;
            TRUNCATE TABLE oc_category;
            TRUNCATE TABLE oc_category_description;
            TRUNCATE TABLE oc_product;
            TRUNCATE TABLE oc_product_description;
            TRUNCATE TABLE oc_product_image;';

            // виробники
            $manufactured = [];

            foreach($items as $item){
                array_push($manufactured, $item->vendor);
            }

            $manufactured = array_values(array_unique($manufactured, SORT_LOCALE_STRING));

            foreach($manufactured as $key => $val){
                $dump .= "INSERT INTO `oc_manufacturer` VALUES ($key+1,'$val','',0);" . PHP_EOL;
            }

            // категорії
            $categories = $xml->categories->category;

            foreach($categories as $category){
                $dump .=  "INSERT INTO `oc_category` VALUES ($category[id],'',0,0,0,0,1,'$time','$time');";

                $cat = addcslashes(mb_strtoupper($category));

                $dump .=  "INSERT INTO `oc_category_description` VALUES ($category[id],1,'$cat','','','','','');" . PHP_EOL;
            }
            
            // айді додаткового фото
            $id = 1;

            foreach($items as $item){

                foreach($manufactured as $key => $val){
                    if($val == $item->vendor){
                        $id_manufactured = $key;
                    }
                }

                $dump .= "INSERT INTO `oc_product` VALUES ($item->partnumber,'$item->art','','','','','','','',999,1,'$item->image',$id_manufactured,1,$item->price,0,1,'$time',0,2,0.00,0.00,0.00,1,1,1,0,1,0,'$time','$time');" . PHP_EOL;

                $description = addcslashes($item->fulldescription);

                $dump .= "INSERT INTO `oc_product_description` VALUES ($id,1,'$item->name','$description','','$item->name','','','');" . PHP_EOL;

                // додаткові фото
                if($item->extraimage){
                    foreach($item->extraimage as $image){
                        $id++;
                        
                        $dump .= "INSERT INTO `oc_product_image` VALUES ($id,$item->partnumber,'$image',0);" . PHP_EOL;
                    }
                }

            }
            
            // view
            echo '<textarea style="width:100%;height:600px;resize:vertical;margin:10px 0 0">'. $dump .'</textarea>';

        } else {
            exit('Не вдалося відкрити файл' . $name);
        }

    }
}

?>

<p style="position:fixed;top:0;right:0;background:maroon;padding:5px;color:white;font-weight:bold">Пам'ять: <?=memory_get_usage()?></p>
</body>
</html>