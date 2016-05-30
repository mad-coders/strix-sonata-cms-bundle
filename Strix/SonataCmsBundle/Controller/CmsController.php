<?php

namespace Strix\SonataCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class CmsController extends Controller
{
    public function indexAction(Request $request)
    {
        $template = $request->attributes->get('_cms_template');

        if (!$template) {
            throw new \RuntimeException('You should define a template in your cms tree');
        }

        return $this->render($template);
    }

    public function routeAction($action, Request $request)
    {
        if (method_exists($this, $action . 'Action')) {
            return call_user_func(array($this, $action . 'Action'), $request);
        }
    }
}