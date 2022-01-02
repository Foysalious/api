<?php

namespace Tests\Console\Commands;

use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use SplFileObject;

class GenerateTestAuthorList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sheba:generate-test-author-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Test Author List';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Exception
     */
    public function handle(): int
    {
        $classes = ClassFinder::getClassesInNamespace('Tests', ClassFinder::RECURSIVE_MODE);
        $test_case_author = [];
        foreach ($classes as $class) {
            $class = new ReflectionClass($class);
            $class_file = new SplFileObject($class->getFileName());
            dump($class_file);
            foreach ($class_file as $line => $content) {
                if ($this->isFunctionMatched($content)) {
                    $git_logs = 'git log --pretty=short -u -L '.$line.','.$line.':'.$class->getFileName().' | grep  "^Author" | sort -u';
                    $commit_hash = trim(exec($git_logs));
                    $name_with_email = explode(": ", $commit_hash);
                    dump($commit_hash, $name_with_email);
                    [$name, $email] = explode("<", $name_with_email[1]);
                    $name = trim($name);
                    $email = str_replace(">", "", $email);

                    $test_case_author[$class->getName()] = [
                        'method'  => $this->getFunctionOrVariableName($content),
                        'line_no' => $line + 1,
                        'author'  => $name,
                        'email'   => $email,
                    ];
                }
            }
        }
        $test_case_author = json_encode($test_case_author, JSON_PRETTY_PRINT);
        File::put(base_path('results/test_case_author.json'), $test_case_author);

        return 0;
    }

    /**
     * @param $content
     * @return false|int
     */
    private function isFunctionMatched($content)
    {
        return preg_match(
            '/
                (private|protected|public) # match visibility or var
                \s                         # followed 1 whitespace
            /x',
            $content
        );
    }

    /**
     * @param $content
     * @return array|string|string[]
     */
    private function getFunctionOrVariableName($content)
    {
        $split_by_space = preg_split("/[\s,]+/", $content);
        if ($split_by_space[3] === "=" || empty($split_by_space[3])) {
            return str_replace("$", "", $split_by_space[2]);
        }

        return str_replace("()", "", $split_by_space[3]);
    }
}
