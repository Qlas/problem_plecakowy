<?php
class Knapsack
{
    private $max_weight, $remain_weight, $value = 0, $items = array();
    function __construct($max_weight)
    {
        $this -> max_weight = $max_weight;
        $this -> remain_weight = $max_weight;
    }
    function return_remain_weight(){return $this -> remain_weight;}
    function return_max_weight(){return $this -> max_weight;}
    function add_items(Item $item)
    {
        $this -> items[] = $item;
        $this -> remain_weight -= $item -> return_weight();
        $this -> value += $item -> return_value();
    }
    function return_items()
    {
        $mask = "|%7.7s |%11.11s | %10.10s |\n";
        printf($mask, 'item_id', 'item_weight', 'item_value');
        foreach($this -> items as $item)
            printf($mask, $item -> return_name(), $item -> return_weight(), $item -> return_value());
        echo "Wartość przedmiotów: ", $this -> value, "\n";
        echo "Waga przedmiotów: ", $this -> max_weight - $this -> remain_weight, "\n";
    }
}

class Item
{
    private $name, $weight, $value, $vperw;
    function __construct($name, $weight,$value)
    {
        $this -> name = $name;
        $this -> weight = $weight;
        $this -> value = $value;
        $this -> vperw = round($value / $weight,2);  #value per weight
    }
    function return_name() {return $this -> name;}
    function return_weight() {return $this -> weight;}
    function return_value() {return $this -> value;}
    function return_vperw() {return $this -> vperw;}
}


function read_csv($file_name) {
    $file = fopen($file_name, "r");
    $items = array();

    while (($row = fgetcsv($file, 0, ";")) != FALSE)
    {
        if($row[0] == "item_id") {continue;}
        if (!is_numeric($row[1]) || !is_numeric($row[2])) 
            {echo "Błąd argumentów, wartość lub waga nie są liczbami w przedmiocie ", $row[0]; exit;}
        if ($row[1] == 0 || $row[2] == 0){echo "Wartość i waga nie mogą być 0"; exit;}
        array_push($items, array($row[0], $row[1], $row[2]));
    }
    $items_array = array();
    foreach($items as $item) #create objects
    {
        $items_array[] = new Item($item[0], $item[1], $item[2]);
    }
    return $items_array;
}

function sorting(Item $a, Item $b)
{
    if ($a -> return_vperw() == $b -> return_vperw()) return 0;
    return ($a -> return_vperw() > $b -> return_vperw()) ? -1 : 1;
}

function greedy($items,$max_weight)
{
    usort($items, 'sorting');
    $knapsack = new Knapsack($max_weight);
    foreach($items as $item)
    {
        if($item -> return_weight() < $knapsack -> return_remain_weight())
        {
            $knapsack-> add_items($item);
        }
    }
    $knapsack -> return_items();
}

function brute_force($items, $max_weight)
{
    $posi = array();
    $sequences = array();
    $value = 0;
    $weight = 0;
    for ($i = 0; $i <= 2**sizeof($items)-1; $i++)
    {
        $actually_value = 0;
        $actually_weight = 0;
        $sequences[$i] =  str_split(str_pad(decbin($i), sizeof($items), 0, STR_PAD_LEFT));
        for ($j = 0; $j < sizeof($sequences[$i]); $j++)
        {
            $actually_value += $sequences[$i][$j] * $items[$j] -> return_value() ;
            $actually_weight += $sequences[$i][$j] * $items[$j] -> return_weight() ;
        }
        if ($actually_weight <= $max_weight && $actually_value > $value)
        {
            $value = $actually_value;
            $weight = $actually_weight;
            $posi = $sequences[$i];
        }
    }
    $knapsack = new Knapsack($max_weight);
    for ($i = 0; $i < sizeof($items); $i++)
    {
        if($posi[$i] != 0)
        {
            $knapsack -> add_items($items[$i]);
        }
    }
    $knapsack -> return_items();

}

function dynamic($items, $max_weight, $n) 
        {  
            $matrix = array(array()); 
            for ($i = 0; $i <= $n; $i++) 
            { 
                for ($w = 0; $w <= $max_weight; $w++) 
                { 
                    if ($i == 0 || $w == 0) 
                        $matrix[$i][$w] = 0; 
                    elseif ($items[$i - 1] -> return_weight() <= $w) 
                            $matrix[$i][$w] = max($items[$i - 1] -> return_value() +  
                            $matrix[$i - 1][$w - $items[$i - 1] -> return_weight()],  
                            $matrix[$i - 1][$w]); 
                    else
                            $matrix[$i][$w] = $matrix[$i - 1][$w]; 
                } 
            }
            $res = $matrix[$n][$max_weight];  
              
            $w = $max_weight; 
            $knapsack = new Knapsack($max_weight);
            for ($i = $n; $i > 0 && $res > 0; $i--)  
            { 
                if ($res != $matrix[$i - 1][$w])  
                { 
                    $knapsack -> add_items($items[$i - 1]);
                    $res = $res - $items[$i - 1] -> return_value(); 
                    $w = $w - $items[$i - 1] -> return_weight(); 
                } 
            } 
            $knapsack -> return_items();
        } 

if (sizeof($argv) != 4) #input length
{
    echo "Zła liczba argumentów";
    exit;
}
if (!is_numeric($argv[2]) || !is_numeric($argv[3])) #max weight and num algorithm is number
{
    echo "Maksymalny udźwig i numer algorytmu muszą być liczbami";
    exit;
}
$items = read_csv($argv[1]);

switch ($argv[3])
{
    case 0:
        echo "Algorytm Zachłanny\n";
        greedy($items, $argv[2]);
        break;
    case 1:
        if(sizeof($items) <= 17) #not more items than 17
        {
            echo "Brute Force\n";
            brute_force($items, $argv[2]);
        }
        else
            echo "Nie można zrobić przeglądu zupełnego powyżej 17 elementów";
        break;
    case 2:
        echo "Dynamic Programming\n";
        dynamic($items, $argv[2], sizeof($items)); 
        break;
    default:
        echo "Zły numer algorytmu";
}
?>