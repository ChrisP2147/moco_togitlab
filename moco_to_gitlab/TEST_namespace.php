<?php

require "vendor/autoload.php";

$project_id = '7430070';
$gitlab_token = 'Vb23WYp2KmxvPG4xVRhB';

$group_array = get_namespace();
var_dump($group_array);

function get_namespace()
{
    global $project_id;
    global $gitlab_token;
    
    $group_array = array();

    $url = 'https://gitlab.com/api/v4/namespaces';
    $response = \Httpful\Request::get($url)->addHeader('Private-Token', $gitlab_token)->expectsJson()->send();

    $namespace_array = (array)json_decode($response, true);

    for ($i = 0; $i < count($namespace_array); $i++)
    {
        $group_array['id'][] = $namespace_array[$i]['id'];
        $group_array['path'][] = $namespace_array[$i]['path'];
        $group_array['name'][] = $namespace_array[$i]['name'];
    }
    return $group_array;
}

?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Namespaces</title>
  <style>
    body {font-family:Verdana, Arial, Helvetica, sans-serif; font-size: 1,5em; font-weight: medium; text-align: center;}
  </style>
</head>

<body>
<br><br>
<?php

    echo "<select class='select_group' name='sel_Group'>";
    for ($i = 0; $i < count($group_array['name']); $i++)
    {
        echo "<option value='" . $group_array['name'][$i] . "'>" . $group_array['name'][$i] . "</option>";
    }
    echo "</select>";

// array(2){
//     [0]=> array(7) {
//         ["id"]=> int(2240725)
//         ["name"]=> string(10) "ChrisP2147"
//         ["path"]=> string(10) "ChrisP2147"
//         ["kind"]=> string(4) "user"
//         ["full_path"]=> string(10) "ChrisP2147"
//         ["parent_id"]=> NULL
//         ["plan"]=> NULL }
//     [1]=> array(8) {
//         ["id"]=> int(3256374)
//         ["name"]=> string(16) "GAL Digital GmbH"
//         ["path"]=> string(11) "gal-digital"
//         ["kind"]=> string(5) "group"
//         ["full_path"]=> string(11) "gal-digital"
//         ["parent_id"]=> NULL
//         ["members_count_with_descendants"]=> int(1)
//         ["plan"]=> NULL } 
//     }


// array(3) {
//     ["id"]=> array(5) {
//         [0]=> int(2240725)
//         [1]=> int(3256374)
//         [2]=> int(3256997)
//         [3]=> int(3257004)
//         [4]=> int(3257008) 
//     }
//     ["path"]=> array(5) {
//         [0]=> string(10) "ChrisP2147"
//         [1]=> string(11) "gal-digital"
//         [2]=> string(8) "kunde-01"
//         [3]=> string(8) "kunde-02"
//         [4]=> string(8) "kunde-03" 
//     }
//     ["name"]=> array(5) {
//         [0]=> string(10) "ChrisP2147"
//         [1]=> string(16) "GAL Digital GmbH"
//         [2]=> string(8) "Kunde 01"
//         [3]=> string(8) "Kunde 02"
//         [4]=> string(8) "Kunde 03"
//     }
// }

?>

 </body>
</html>