<?php

namespace Strix\SonataCmsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    public function fileAction(Request $request)
    {
        $uploadDirectory = $this->container->getParameter('kernel.root_dir') . '/../web/uploads/';

        if ($request->files->has('file') &&
            is_uploaded_file($request->files->get('file'))) {

            //upload file
            $file = $request->files->get('file');

            $targetFilename = md5(rand() . time()) . '.' . $file->getClientOriginalExtension();

            move_uploaded_file($file, $uploadDirectory .  $targetFilename);

            return new Response('uploads/' . $targetFilename);
        } else {
            return new Response('ERROR');
        }

    }
}