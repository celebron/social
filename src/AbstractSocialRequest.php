<?php

namespace Celebron\socialSource;

use Celebron\socialSource\interfaces\SocialRequestInterface;
use yii\base\Component;

class AbstractSocialRequest extends Component implements SocialRequestInterface
{
    public function __construct (
        public readonly string $name,
        public readonly Configuration $configure,
        $config = [])
    {
        parent::__construct($config);
    }
}