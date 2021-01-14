<?php
// temp
function pre($array)
{
    echo '<pre>';
        print_r($array);
    echo '</pre>';
}
?>
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

todo: 7: видалення файлу через 10 хвилин
*/

$message = ''; // del?

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

            // goods
            $items = $xml->items->item;

            // часова мітка
            $time = date('Y-m-d h:i:s', time());

            // основний файл дампу
            $dump = '';

            // виробники
            $manufactured = [];
            foreach($items as $item){
                array_push($manufactured, $item->vendor);
            }
            $manufactured = array_values(array_unique($manufactured, SORT_LOCALE_STRING));
            foreach($manufactured as $key => $val){
                $dump .= "INSERT INTO `oc_manufacturer` VALUES ($key,'$val','',0);";
            }

            // категорії
            $categories = $xml->categories->category;

            foreach($categories as $category){
                $dump .=  "INSERT INTO `oc_category` VALUES ($category[id],'',0,0,0,0,1,'$time','$time');";
                $cat = mb_strtoupper($category);
                $dump .=  "INSERT INTO `oc_category_description` VALUES ($category[id],1,'$cat','','','','','');";
            }

            // товари
            $products = '';

            // айді товара
            $id = 1;
            foreach($items as $item){
                $id++;
                $dump .= "INSERT INTO `oc_product` VALUES (
                    $id,
                    '$item->art',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    999, -- quantity
                    1, -- stock_status_id
                    '$item->image',
                    5, -- manufactured 
                    1, -- shipping
                    $item->price, -- price
                    0, -- points
                    9, -- tax_class_id
                    '$time', -- date_available
                    0, -- weight
                    2, -- weight_class_id
                    0.00, -- length
                    0.00, -- width
                    0.00, -- height
                    1, -- length_class_id
                    1, -- subtract
                    1, -- minimum
                    0, -- sort_order
                    1, -- status
                    0,
                    '$time', -- date_added
                    '$time' -- date_modified
                );";
                $dump .= "INSERT INTO `oc_product_description` VALUES (
                    $id,
                    1,
                    '$item->name',
                    '$item->fulldescription',
                    '',
                    '$item->name',
                    '',
                    '',
                    ''
                );";

                foreach($item->extraimage as $image_id => $images){
                    $dump .= "INSERT INTO `oc_product_image` VALUES (
                        $image_id, -- product_image_id
                        $id, -- product_id
                        '$images', -- image
                        0
                    );";
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