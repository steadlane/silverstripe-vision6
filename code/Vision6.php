<?php

/**
 * Class Vision6
 */
class Vision6 extends Object
{

    /**
     * @param Vision6List|int $listOrId
     * @return FieldList|Object
     */
    public function getFieldsForList($listOrId)
    {
        $factory = Vision6FieldFactory::create();
        return $factory->setList($listOrId)->build();
    }

    /**
     * @param $name
     * @param FieldList $fields
     * @param FieldList|null $actions
     * @param Validator|null $validator
     *
     * @return Form
     */
    public function createForm($name, FieldList $fields, FieldList $actions = null, Validator $validator = null)
    {
        if (!$actions) {
            $actions = FieldList::create(
                array(
                    FormAction::create('subscribe', 'Subscribe')->setUseButtonTag(true)
                )
            );
        }

        $form = Form::create(new Vision6PageController(), $name, $fields, $actions, $validator);

        if ($form->hasExtension('FormSpamProtectionExtension')) {
            $form->enableSpamProtection();
        }

        $this->extend('updateForm', $form);

        return $form;
    }


    /**
     * Checks to see if the email is already in the Vision6 List
     * @param $listId
     * @param $emailAddress
     *
     * @return bool
     */
    public function isEmailInList($listId, $emailAddress)
    {
        $api = Vision6Api::create();
        $contacts = $api->invokeMethod(
            'searchContacts',
            $listId,
            array(
                array(
                    'Email',
                    'exactly',
                    $emailAddress
                )
            )
        );

        return (!empty($contacts));
    }

    /**
     * Gets the session container or creates it if it doesn't exist.
     *
     * @return array|mixed|null|\Session
     */
    public function getSession()
    {
        return \Session::get('Vision6') ?: (\Session::set('Vision6', array())) ?: \Session::get('Vision6');
    }

    /**
     * Sets the session container
     *
     * @param array $data
     *
     * @return void
     */
    public function setSession(array $data)
    {
        \Session::set('Vision6', $data);
        \Session::save();
    }
}
