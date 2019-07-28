<?php namespace Sheba\Dev;

use Exception;

class DevelopmentEnvironmentChecker
{
    /**
     * @throws Exception
     */
    public function check()
    {
        $this->checkNpmPackages();
        $this->checkGitHooks();
    }

    /**
     * @throws Exception
     */
    private function checkNpmPackages()
    {
        exec('npm list --depth=0 --json=true', $installed);
        $installed = json_decode(implode($installed), true);
        $required = json_decode(file_get_contents(base_path() . '/package.json'), true);
        if (array_key_exists('problems', $installed)) {
            throw new Exception('There are outdated npm packages. Run `npm update` first.');
        }

        $has_missing_dependency = $installed && array_key_exists('dependencies', $installed) ? false : true;
        if (!$has_missing_dependency) {
            foreach ($required['devDependencies'] as $dependency => $version) {
                if (!array_key_exists($dependency, $installed['dependencies'])) {
                    $has_missing_dependency = true;
                    break;
                }
            }
        }
        if ($has_missing_dependency) {
            throw new Exception('There are missing npm packages. Run `npm install` first.');
        }
    }

    /**
     * @throws Exception
     */
    private function checkGitHooks()
    {
        $required = [
            'commit-msg'
        ];
        foreach ($required as $file) {
            if (!file_exists(base_path() . "/.git/hooks/$file")) {
                throw new Exception('There are missing git hooks. Remove node_modules and run `npm install` again.');
            }
        }
    }
}
