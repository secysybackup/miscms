<?php

class UploadimgWidget extends Widget
{
    public function render($data)
    {
        $content = $this->renderFile('uploadimg', $data);

        return $content;
	}

}