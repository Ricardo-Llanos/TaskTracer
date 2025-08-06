<?php
require_once(".env/config.php");

class RegularExpression{

    public function __construct(){
    }

    public function createRegExr(string $root) : array{
        $data = file_get_contents($root);
        $data = json_decode($data);

        if (json_last_error() != JSON_ERROR_NONE){
            $returnArray=[
                "StatusCode"=> 500,
                "StatusMessage"=> "El json brindado no pudo ser decodificado. " . json_last_error_msg()
            ];
        }

        $regular_expression = "";
        foreach($data as $field){
            if (is_array(trim($field))){
                $regular_expression.=$field;
            }
        }

        if (!$regular_expression){
            $returnArray=[
                "StatusCode"=> 202,
                "StatusMessage"=> "La RegExr fue creada exitosamente.",
                "Data"=> $regular_expression
            ];
        }else{
            $returnArray=[
                "StatusCode"=> 409,
                "StatusMessage"=> "La RegExr presentó errores en su creación.",
                "Data"=> $regular_expression
            ];
        }

        return $returnArray;
    }
}
?>