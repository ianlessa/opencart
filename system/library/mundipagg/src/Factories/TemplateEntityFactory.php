<?php
namespace Mundipagg\Factories;


use Mundipagg\Aggregates\Template\TemplateEntity;

class TemplateEntityFactory
{

    /**
     * @param $postData
     * @return TemplateEntity
     */
    public function createFromPostData($postData)
    {
        $template = new TemplateEntity();
    }
}