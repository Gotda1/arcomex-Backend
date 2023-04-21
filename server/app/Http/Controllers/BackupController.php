<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\DbDumper\Databases\MySql;

class BackupController extends Controller
{
    public function backupDB(){
        MySql::create()
        ->setDbName(env("DB_DATABASE"))
        ->setUserName(env("DB_USERNAME"))
        ->setPassword(env("DB_PASSWORD"))
        ->dumpToFile('dump.sql');
        
    }

    public function test(){
         // LAMINADOS
         $path = storage_path() . "/FALTANTES.xlsx";
         $reader = \PhpOffice\PhpSpreadsheet\IOFactory::load($path);
         $sheet = $reader->getActiveSheet();
         $highestRow = $sheet->getHighestRow(); 
         $highestColumn = $sheet->getHighestColumn();

         $sql = "";
        for ($row = 2; $row <= $highestRow; $row++){ 
            $clavePT = $sheet->getCell("A$row")->getValue();
            $formula = $sheet->getCell("C$row")->getValue();
            if( $formula == "SELECCIONA FORMULA") continue;

            $claveform = explode("   ", $formula)[1];
            // $sql .="$formula <br>";
            $sql .= "UPDATE producto_terminado AS pt SET pt.formula_id = (SELECT f.id FROM formulas AS f WHERE f.clave = '$claveform') WHERE pt.clave = '$clavePT' AND pt.formula_id = 0;  <br>";
        
        }

        echo $sql;
    }
    
}
