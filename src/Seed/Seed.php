<?php

namespace PhinxEloquent\Seed;

use Phinx\Seed\AbstractSeed;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint as Blueprint;
use Illuminate\Support\Collection;
use PHPExcel;
use PHPExcel_IOFactory;

class Seed extends AbstractSeed
{
     
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeders is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     */
     
    public $capsuleManager;
    public $schema;
    public $excel = null;
    public $uploads_path = null;
    public $xls_file = null;
     
    public function run() {
        
     
        
        
    } 
    
    public function run_sample() {
        
        $this->autoload();
        $this->uploads_path = __DIR__ . '/../../../../public/assets/uploads/images/';
        $this->xls_file = __DIR__ . '/import.xls';
        
        $this->ORMConnect();
        
        $tables = array(); //meter table names aca
        $this>truncate_everything($tables);
        
        //unguard all models
        \Illuminate\Database\Eloquent\Model::unguard();
        
        //load excel
        if (!$this->getExcel($this->xls_file)) {
            die('XLS file not found. KIK');
        };
        
        $this->sampleSeed();
        
        
    } 
    
    public function truncate_everything($tables) {
        
       
        
        foreach ($tables as $tablex) {
            $tableAdapter = new Phinx\Db\Adapter\TablePrefixAdapter($this->getAdapter());
            $tableName = $tablex;
            $fullTableName = $tableAdapter->getAdapterTableName($tableName);

            if (ENVIRONMENT !== 'production') {
                $this->getAdapter()->execute(
                    'TRUNCATE TABLE ' . $fullTableName
                );
            }

        }
    }
     
    public function ORMConnect() {
        
        $this->capsuleManager = new CapsuleManager;
        $this->capsuleManager->addConnection(array(
            'driver'    => 'mysql',
            'host' => getenv('database.default.hostname'),
            'database' => getenv('database.default.database'),
            'username' => getenv('database.default.username'),
            'password' => getenv('database.default.password'),
            'charset' => getenv('database.default.charset'),
            'collation' => getenv('database.default.DBCollat'),
            'prefix' => getenv('database.default.DBPrefix')
        ));

        $this->capsuleManager->setEventDispatcher(new Dispatcher(new Container));
        $this->capsuleManager->getConnection()->enableQueryLog();
        $this->capsuleManager->setAsGlobal();
        $this->capsuleManager->bootEloquent();
        $this->schema = $this->capsuleManager->schema();
    }
    
    public static function autoload($dir) 
	{	$files = [];
        foreach( glob($dir.'/../../../*/models/*.php') as $file ){ 
         if( !strrpos($file,'/admin')) { 
           include_once $file;
          }
        } 
	
	}
     
    public function slugify($text)
    {
      $text = preg_replace('~[^\pL\d]+~u', '-', $text);
      $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
      $text = preg_replace('~[^-\w]+~', '', $text);
      $text = trim($text, '-');
      $text = preg_replace('~-+~', '-', $text);
      $text = strtolower($text);
    
      if (empty($text)) {
        return 'n-a';
      }
    
      return $text;
    }
    
      function getXml($url)
    {
        echo "Fetching XML from '{$url}'..." . PHP_EOL;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($ch);
        curl_close($ch);
        $xml = simplexml_load_string($output);
        return $xml;
    }

    public function getExcel($filename)
    {

        $inputFileType = PHPExcel_IOFactory::identify($filename);
        $objReader = PHPExcel_IOFactory::createReader($inputFileType); 
        $this->excel = $objReader->load($filename); 
        return true;

    }

   public function getWorkSheetData($worksheet = null, $range = null)
    {
        if ($worksheet !== null) {
            $objWorksheet = $this->excel->getSheet($worksheet);
            $maxCell = $objWorksheet->getHighestRowAndColumn();
             if ($range !== null) {
                $data = $objWorksheet->rangeToArray($range);  
            }else{
                $data = $objWorksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);    
            }
            $data = $objWorksheet->rangeToArray('A1:' . $maxCell['column'] . $maxCell['row']);
            $all_rows = array();
            $header = null;
            foreach ($data as $row) {
                if ($header === null) {
                    $header = $row;
                } else {
                }
                $all_rows[] = array_combine($header, $row);
            }
            return $all_rows;
        } else {
            return null;
        }
    }
    
    public function sampleSeed(){
        $worksheet_num = 0;
        $all_rows = $this->getWorkSheetData($worksheet_num); //return eloquent collection OR null.
         
        $c = new Collection($all_rows);
        $data = $c->all();
        file_put_contents(__DIR__.'/worksheet_data_num_'.$worksheet_num.'json',print_r($data,true));
        echo "Sample Seed run";
        
        // for ($x = 1; $x < count($data); $x++) {
        //     if ( $data[$x]["title"]) { //no procesar si esta es null.
        //         $sub = [];
        //         $sub["title"] = $data[$x]["title"];
        //         $sub["slug"] = $data[$x]["slug"];
        //         $sub["icon_svg"] = $data[$x]["img"];
        //         $related_type = $this->getType('Related_type','title',$data[$x]['related_title']);
        //         if ($related_type) {
        //             $sub["related_id"] = $related_type->id;    
        //         }
        //         Sample_type::create($sub);
        //     }
        // }
                    
    }        
     
    public function getType($type,$field,$value) {
          
           return $type::where($field,trim($value))->get()->first();
           
       }
    
    
}
