<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/product.css">
    <title>Product</title>
</head>
<body>
    <?php
        require_once 'lib/Finity/Autoloader.php';
        $prolist= new \Finity\Product\ItemManager();

        if($_SERVER['REQUEST_METHOD'] == 'GET'){
           $selectedCat = (isset($_GET['q'])?$_GET['q']:'');
            
        }else{
            $selectedCat = '';
        }

    ?> 
    <div class="item-wrapper">
        <!-- this floats to the left with categories -->
        <div class="sidebar">
            
            <div class="catlist">
                <div class="title">Category</div>

                <?php include 'product/category_list.php';?> 
            </div>
        </div> 

        <!-- area for items to display to user-->
        <div class="item-container">
            <?php include 'product/list.php'; ?>
        </div>

    </div>
</body>
</html>