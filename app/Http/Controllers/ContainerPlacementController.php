<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContainerPlacementController extends Controller
{
    function index(Request $request,$id)
    {
        return self::run($id);
    }

    function checkSimilairity($trigit){
        // $k;
        for ($i=0;$i < strlen($trigit); $i++) {
            $d=$trigit[$i];
            if(isset($k) && $k!=$d){
                return false;
            }
            $k=$d;
        }
        return true;
    }
    
    function isSorted($int){
        $s = (int) $int[0];
        $e = (int)$int[1];
        
        if($s < $e && $s+1 == $e) return true;
        return false;
    }
    
    function primesCheck($a)
    {
        $a = (int) $a;
    
        $k=0;
        for($b=1;$b<=$a;$b++){
            if($a % $b == 0) $k++;
        }
        if($k == 2){
            return true;
        }
        return false;
    }
    

    //helper randomNumber(7) dapat melakukan generate $id
    static function run($id){

        if (!is_numeric($id) || strpos($id,0) !== FALSE) return "DEAD";
        
        $primes = self::primesCheck($id);
        if($primes){
            if (self::checkSimilairity(substr($id,-3))) return 'RIGHT';
            
            elseif(self::isSorted(substr($id,-2))) return 'LEFT';
            
            elseif(self::primesCheck(substr($id,3))) return 'CENTRAL';
            
        }else return 'DEAD';
    }
}
