<?php

namespace BitWeb\Zend\View\Helper;

use Zend\Form\Fieldset;
use Zend\Form\Form;
use Zend\View\Helper\AbstractHelper;
use Zend\View\Model\ViewModel;

class Messages extends AbstractHelper
{

    private $templatesFolder = 'messages';
    private $defaultFormErrorTitle = 'An error has occured!';
    private $messages = array();
    private $form = null;

    public function __invoke(array $messages = null)
    {
        if ($messages != null) {
            if ($this->checkMessages($messages)) {
                $this->messages = $messages;
            }
        }

        return $this;
    }

    public function setTemplatesFolder($templatesFolder)
    {
        $this->templatesFolder = $templatesFolder;
    }

    public function form(Fieldset $form)
    {
        $this->form = $form;

        return $this;
    }

    protected function hasFormErrors(Form $form)
    {
        foreach ($form->getFlatMessages() as $element) {
            if (count($element) > 0) {
                return true;
            }
        }

        return false;
    }

    public function error($title = null, $description = null, array $messages = array())
    {
        if ($this->form != null) {

            return $this->formError($this->form, $title, $description);
        }

        $this->messages[] = new ErrorMessage($title, $description, $messages);

        return $this;
    }

    public function errors($title = null, $description = null)
    {
        if ($this->form != null) {

            return $this->formErrors($this->form, $title, $description);
        }
        return $this;
    }

    public function formError(Form $form, $title = null, $description = null)
    {
        $this->form($form);
        if ($title == null) {
            $title = $this->defaultFormErrorTitle;
        }

        if ($this->hasFormErrors($form)) {
            $this->messages[] = new FormErrorMessage($title, $description, $form->getMessages());
        }

        return $this;
    }

    public function formErrors(Fieldset $form, $title = null, $description = null)
    {
        $this->form($form);
        if ($title == null) {
            $title = $this->defaultFormErrorTitle;
        }

        if ($form instanceof AbstractForm) {
            if ($this->hasFormErrors($form) != null) {
                $this->messages[] = new FormErrorsMessage($title, $description, $form->getFlatMessages());
            }
        } elseif ($form instanceof Fieldset) {
            if (count($form->getMessages()) > 0) {
                $this->messages[] = new FormErrorsMessage($title, $description, $form->getMessages());
            }
        }
        /*if ($form->getFlatMessages() != null) {
            $error = new FormErrorsMessage($title, $description, $form->getFlatMessages());
            $this->messages[] = $error;
        }*/

        return $this;
    }

    public function success($title = null, $description = null, array $messages = array())
    {
        $this->messages[] = new SuccessMessage($title, $description, $messages);

        return $this;
    }

    public function info($title = null, $description = null, array $messages = array())
    {
        $this->messages[] = new InfoMessage($title, $description, $messages);

        return $this;
    }

    public function warning($title = null, $description = null, array $messages = array())
    {
        $this->messages[] = new WarningMessage($title, $description, $messages);

        return $this;
    }

    public function __toString()
    {
        try {
            $render = $this->render();
        } catch (\Exception $e) {
            $render = $e->getMessage();
        }

        return $render;
    }

    public function render()
    {
        $renderings = '';

        foreach ($this->messages as $key => $message) {
            $messageType = $this->assembleTypeFromClassName(get_class($message));
            $this->checkTypeAvailable($messageType);

            $view = new ViewModel();
            $view->setTemplate('helper/' . $this->templatesFolder . '/' . self::assembleTemplateName($messageType));
            $view->title = $message->title;
            $view->description = $message->description;
            $view->messages = $message->messages;

            $view->form = $this->form;

            $renderings .= $this->getView()->render($view);
            unset($this->messages[$key]);
        }

        $this->form = null;

        return $renderings;
    }

    /**
     * @return the $defaultFormErrorTitle
     */
    public function getDefaultFormErrorTitle()
    {
        return $this->defaultFormErrorTitle;
    }

    /**
     * @param string $defaultFormErrorTitle
     */
    public function setDefaultFormErrorTitle($defaultFormErrorTitle)
    {
        $this->defaultFormErrorTitle = $defaultFormErrorTitle;
    }

    protected static function assembleTemplateName($messageType)
    {
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '-$1', $messageType));
    }

    protected function checkTypeAvailable($messageType)
    {
        if (!method_exists($this, $messageType)) {
            throw new \InvalidArgumentException('Message type "' . $messageType . '" is invalid, a corresponding method doesn\'t exist');
        }
    }

    protected function checkMessages($messages)
    {
        foreach ($messages as $message) {
            if (!$message instanceof Message) {
                return false;
            }
        }

        return true;
    }

    protected static function assembleTypeFromClassName($className)
    {
        $typeName = lcfirst(substr($className, strrpos($className, '\\') + 1, strlen('Message') * -1));

        return $typeName;
    }
}
