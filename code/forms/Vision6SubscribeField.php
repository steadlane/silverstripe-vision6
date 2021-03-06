<?php

/**
 * Class Vision6SubscribeField
 *
 * This field is decoupled and can be used independently in any form
 *
 * @author Reece Alexander <reece@steadlane.com.au>
 */
class Vision6SubscribeField extends CheckboxField
{
    /** @var int Vision6 List ID */
    protected $listId;

    /** @var string The field name within the parent form that contains the email address */
    protected $emailFieldName;

    /** @var bool Allow "already subscribed" errors to be gracefully ignored */
    protected $gracefulReject = false;

    /**
     * @return array
     */
    public function getAttributes()
    {
        if (!$this->listId) {
            user_error(
                'You must provide a Vision6 List ID',
                E_USER_ERROR
            );
        }

        if (!$this->emailFieldName) {
            user_error(
                'You must provide the fieldName of the email field in this form',
                E_USER_ERROR
            );
        }

        $attributes = parent::getAttributes();

        $attributes = array_merge(
            $attributes,
            array(
                'data-v6-list-id' => $this->listId,
                'data-v6-email-field' => $this->emailFieldName
            )
        );

        return $attributes;
    }

    /**
     * Set the list ID that the email will be subscribed too
     *
     * @param $listId
     * @return $this
     */
    public function setListId($listId)
    {
        $this->listId = $listId;

        return $this;
    }

    /**
     * Sets the field name that should be holding the email address, this field can be hidden
     * but must exist
     *
     * @param $fieldName
     * @return $this
     */
    public function setEmailFieldName($fieldName)
    {
        $this->emailFieldName = $fieldName;

        return $this;
    }

    /**
     * If the email address is already subscribed, the user will be returned to the form
     * with an error message, to gracefully allow subscriptions to fail (where could be
     * semantically desired) set this to true
     *
     * @param $bool
     * @return $this
     */
    public function setGracefulReject($bool)
    {
        $this->gracefulReject = (bool)$bool;

        return $this;
    }

    /**
     * @param Validator $validator
     * @return bool
     */
    public function validate($validator)
    {
        $form = $this->getForm();
        $data = $form->getData();

        /** @var TextField|EmailField $emailField */
        $email = $data[$this->emailFieldName];

        if (!$email) {
            user_error(
                _t(
                    'Vision6.FIELD_NOT_FOUND_IN_FORM',
                    'The field {field_name} was not found in {form_name}',
                    'The message that is displayed when the defined "Email" field is not found in the form',
                    array(
                        'field_name' => $this->emailFieldName,
                        'form_name' => $form->getName()
                    )
                ),
                E_USER_ERROR
            );
        }

        if ($this->gracefulReject) {
            return true;
        }

        if (Vision6::singleton()->isEmailInList($this->listId, $email)) {
            $validator->validationError(
                $this->name,
                _t(
                    'Vision6.SUBSCRIBE_ALREADY',
                    'That email address has already been subscribed'
                ),
                "validation"
            );

            return false;
        }

        return true;
    }
}