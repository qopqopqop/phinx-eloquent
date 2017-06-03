<?php

namespace PhinxEloquent;

use Phinx\Migration\AbstractMigration;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Schema\Blueprint as Blueprint;

class Migration extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
     
    public $capsuleManager;
    public $schema;
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
    
    public function up() {
    
        $this->ORMConnect();
        
    }
    
    public function down() {
        
        $this->ORMConnect();
        
    }
}
