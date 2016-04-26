<?php


echo "<br>**********Forma 1 Solo contar dias y extraer la fecha  ****************<br>";

$fecha = date('Y-m-j');
$nuevafecha = strtotime ( '+2 day' , strtotime ( $fecha ) ) ;
$nuevafecha = date ( 'Y-m-j' , $nuevafecha );
 
echo "<br>********** ".$nuevafecha."  ****************<br>";



echo "<br>**********Forma 2 ****************<br>";

$fecha = '2016-04-26'; //formtao sql yyyy-mm-dd
$cantidadDiasVacaciones = 3;
$cantidadDias = 5;
$cantidadDiasValidos = 0;
$cantidadDiasFeriados = 0;
$fechaSalida = "";


print_r(dias_habiles($fecha, $cantidadDias ));

function dias_habiles($fecha, $cantidadDias ){

    $cantidadDiasValidos = 0;
    $cantidadDiasFeriados = 0;
    $fechaSalida = "";

    while(true){

        if(is_dia_valido($fecha)){
            $cantidadDiasValidos++;

            if($cantidadDiasValidos == $cantidadDias){
                $fecha;
                $cantidadDiasValidos;
                $cantidadDiasFeriados;

                $arrFecha = array("cal_fecha"=>$fecha, "cantidad_dias_validos"=>$cantidadDiasValidos, "cantidad_dias_feriados"=>$cantidadDiasFeriados);
                return $arrFecha;
                break;
            }else{
                $fecha = date_create($fecha);
                $fecha = date_add($fecha, date_interval_create_from_date_string('1 days'));
            }
        }else{
            $cantidadDiasFeriados++;
            $fecha = date_create($fecha);
            $fecha = date_add($fecha, date_interval_create_from_date_string('1 days'));
        }

        $fecha =  date_format($fecha, 'Y-m-d');
    }


}//fin de la funcion


function is_dia_valido($fecha){

    $weekday = date("w", strtotime($fecha));

    if($weekday == 0 || $weekday == 6){
        return false;
    }else{
        $feriados  = array(
            '01-01',
            '10-04',
            '11-04',
            '01-05',
            '21-05',
            '29-06',
            '16-07',
            '15-08',
            '18-09',
            '19-09',
            '12-10',
            '31-10',
            '01-11',
            '08-12',
            '13-12',
            '25-12');

        $fecha = explode("-", $fecha);
        $fecha = $fecha[2]."-".$fecha[1]; // deberia devolver DIA-MES

        if(in_array($fecha, $feriados)){
            return false;
        }
        else{
            return true;
        }
    }
}




?>