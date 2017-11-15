<?php declare(strict_types=1);

namespace script;

use lzx\App;
use lzx\db\DB;

require_once dirname(__DIR__) . '/lib/lzx/App.php';

/**
 * Description of FundImporter
 *
 * @author ikki
 */
class FundImporter extends App
{
    private $fundFamilies = [];
    private $fundCategories = [];

    public function __construct()
    {
        parent::__construct();
        // register current namespaces
        $this->loader->registerNamespace(__NAMESPACE__, __DIR__);

        // database connection
        DB::getInstance([
            'dsn' => 'mysql:unix_socket=/var/run/mysqld/mysqld.sock;dbname=finance;charset=utf8',
            'user' => 'web',
            'password' => 'Ab663067',
        ]);
    }

    public function run($argc, array $argv)
    {
        // check filename argument
        if ($argc < 2) {
            exit('ERROR: no input file!' . \PHP_EOL);
        }
        $filename = $argv[1];
        if (!is_file($filename)) {
            exit('ERROR: input file does not exist!' . \PHP_EOL);
        }

        $data = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $n = sizeof($data);
        $i = 0;

        while ($i < $n) {
            // Fidelity Fund
            if ($data[$i++] == "\tFidelity Fund\t") {
                // family
                $family = 'Fidelity';
                if (!array_key_exists($family, $this->fundFamilies)) {
                    // create object
                    $fundFamily = new FundFamily();
                    $fundFamily->name = $family;

                    // check if exist
                    $fundFamily->load('id');
                    if (!$fundFamily->exists()) {
                        // add to database
                        $fundFamily->add();
                    }

                    // add to array
                    $this->fundFamilies[$family] = $fundFamily;
                } else {
                    $fundFamily = $this->fundFamilies[$family];
                }

                $name = $data[$i++];
                $category = trim($data[$i++]);

                while (substr($data[$i], 0, 5) != " Buy ") {
                    $i++;
                }
                $symbol = trim(substr($data[$i++], 5));

                $name = str_replace([' (' . $symbol . ')', '®'], '', $name);

                if (!array_key_exists($category, $this->fundCategories)) {
                    $fundCategory = new FundCategory();
                    $fundCategory->name = $category;
                    $fundCategory->load('id');
                    if (!$fundCategory->exists()) {
                        $fundCategory->add();
                    }

                    $this->fundCategories[$category] = $fundCategory;
                } else {
                    $fundCategory = $this->fundCategories[$category];
                }

                $fund = new Fund();
                $fund->cid = $fundCategory->id;
                $fund->fid = $fundFamily->id;
                $fund->name = $name;
                $fund->symbol = $symbol;

                $fund->load('id');
                if ($fund->exists()) {
                    print 'ERROR: trying to add duplicate fund: ' . $fund . \PHP_EOL;
                } else {
                    $fund->add();
                }
            }
        }
    }
}

// main function
$importer = new FundImporter();
$importer->run($argc, $argv);
