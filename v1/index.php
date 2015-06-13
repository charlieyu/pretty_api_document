<?php 
$xmlfile = dirname(__FILE__).'/'.$_GET["file"];

$xml = simplexml_load_file($xmlfile);
$xmlJson = json_encode($xml);
$xmlArray = json_decode($xmlJson, true); 

if (array_key_exists("name", $xmlArray["api"])){
    $xmlArray["api"] = [$xmlArray["api"]];
}

foreach($xmlArray["api"] as $key => $api){
    if (array_key_exists("name", $api["param"])){
        $xmlArray["api"][$key]["param"] = [$api["param"]];
    }
}

foreach($xmlArray["api"] as $key => $api){
    if (array_key_exists("http_code", $api["response"])){
        $xmlArray["api"][$key]["response"] = [$api["response"]];
    }
}

$bgClass = ["200"=>"bg-success","401"=>"bg-warning", "400"=>"bg-warning"];

?>

<!DOCTYPE html>

<html>
<head>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/include.php'?>
    <style type="text/css">
.bg-success {
    padding: 10px;
    border-radius: 8px;
    font-weight: bold;
}

.bg-warning {
    padding: 10px;
    border-radius: 8px;
    font-weight: bold;
}

<?php 
foreach ($xmlArray["api"] as $api){
    echo "#".$api["name"].":target { padding-top:120px; }"."\n";
}

?>


</style>

</head>
<body>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/v1/header.php'?>
    <nav class="nav navbar-default navbar-fixed-top">
        <div class="container">
            <h3><?php echo $xmlArray["title"]?></h3>
            <h4>
                Version: <?php echo $xmlArray["version"]?> ; 
                Location: <a href="<?php echo $xmlArray["location"]?>"><?php echo $xmlArray["location"]?></a>
            </h4>
        </div>
    </nav>
    <div class="container" style="margin-top:120px;">
        <div class="row">
            <div class="col-md-2">
                <ul class="nav nav-list affix">
                    <li class="nav-header">MENU</li>
                    <?php 
                    foreach ($xmlArray["api"] as $api){
                    ?>
                    <li><a href="<?php echo "#".$api['name']?>"><?php echo $api['path']?></a></li>
                    <?php 
                    }
                    ?>
                </ul>
            </div>
            <div class="col-md-10">
                <?php foreach ($xmlArray["api"] as $api) { ?>
                <!-- <?php echo $api["path"] ?> -->
                <div class="panel panel-primary" id="<?php echo $api["name"] ?>">
                    <div class="panel-heading">
                        <button type="button" class="btn btn-default"><?php echo $api["method"]?></button>
                        <span style="font-size: 18px;"><?php echo $api["path"]?></span>
                    </div>
                    <div class="panel-body">
                        <h4>Param</h4>
                        <table class="table table-striped table-hover">
                            <tr>
                                <th>name</th>
                                <th>Located</th>
                                <th>Description</th>
                                <th>Required</th>
                                <th>Schema</th>
                            </tr>
                            <?php foreach ($api["param"] as $param) { ?>
                            <tr>
                                <td><?php echo $param["name"]; ?></td>
                                <td><?php echo $param["located"]; ?></td>
                                <td><?php echo $param["description"]?></td>
                                <td><?php echo $param["required"]; ?></td>
                                <td><?php echo $param["schema"]; ?></td>
                            </tr>
                            <?php } ?>
                        </table>
                        <h4>Response</h4>
                        <?php foreach ($api["response"] as $response) { ?>                  
                        <p class="<?php echo $bgClass[$response["http_code"]]?>">Http Code: <?php echo $response["http_code"]; ?></p>
                        <?php echo $response["content_type"]?><br />
                        <pre><?php echo prettyPrint($response["content"])?></pre>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php include $_SERVER['DOCUMENT_ROOT'] . '/v1/footer.php'?>
</body>
</html>

<?php 
function prettyPrint( $json )
{
    $result = '';
    $level = 0;
    $prev_char = '';
    $in_quotes = false;
    $ends_line_level = NULL;
    $json_length = strlen( $json );

    for( $i = 0; $i < $json_length; $i++ ) {
        $char = $json[$i];
        $new_line_level = NULL;
        $post = "";
        if( $ends_line_level !== NULL ) {
            $new_line_level = $ends_line_level;
            $ends_line_level = NULL;
        }
        if( $char === '"' && $prev_char != '\\' ) {
            $in_quotes = !$in_quotes;
        } else if( ! $in_quotes ) {
            switch( $char ) {
                case '}': case ']':
                    $level--;
                    $ends_line_level = NULL;
                    $new_line_level = $level;
                    break;

                case '{': case '[':
                    $level++;
                case ',':
                    $ends_line_level = $level;
                    break;

                case ':':
                    $post = " ";
                    break;

                case " ": case "\t": case "\n": case "\r":
                    $char = "";
                    $ends_line_level = $new_line_level;
                    $new_line_level = NULL;
                    break;
            }
        }
        if( $new_line_level !== NULL ) {
            $result .= "\n".str_repeat( "    ", $new_line_level );
        }
        $result .= $char.$post;
        $prev_char = $char;
    }

    return $result;
}
?>