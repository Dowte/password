<?php

namespace Dowte\Password\pass;

use Dowte\Password\forms\PasswordForm;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SymfonyApplication extends Application
{
    public function run(InputInterface $input = null, OutputInterface $output = null)
    {
        // 将为找不到的命令以搜索密码形式查找
        $input = new ArgvInput();
        try {
            $name = $this->getCommandName($input);
            $this->find($name);
        } catch (\Exception $e) {
            if (($e->getMessage() == 'Command "'.$name.'" is not defined.' ) && in_array($name, PasswordForm::pass()->getDecryptedKey())) {
                $input = new ArgvInput(['', 'find', $name]);
            }
        }

        return parent::run($input, $output); // TODO: Change the autogenerated stub
    }
}