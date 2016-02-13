<?php
class HtmlPet_WebForms_Block_Adminhtml_Webforms_Edit_Tab_Email
    extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $renderer = $this->getLayout()->createBlock('webforms/adminhtml_element_field');
        $form->setFieldsetElementRenderer($renderer);
        $form->setFieldNameSuffix('form');
        $form->setDataObject(Mage::registry('webforms_data'));

        $this->setForm($form);

        $fieldset = $form->addFieldset('email_settings', array(
            'legend' => Mage::helper('webforms')->__('E-mail Settings')
        ));

        $fieldset->addField('add_header', 'select', array(
            'label'  => Mage::helper('webforms')->__('Add header to the message'),
            'title'  => Mage::helper('webforms')->__('Add header to the message'),
            'name'   => 'add_header',
            'note'   => Mage::helper('webforms')->__('Add header with Store Group, IP and other information to the message'),
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset->addField('email_reply_template_id', 'select', array(
            'label'    => Mage::helper('webforms')->__('Reply template'),
            'title'    => Mage::helper('webforms')->__('Reply template'),
            'name'     => 'email_reply_template_id',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('E-mail template for replies'),
            'values'   => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $fieldset->addField('email_smtpvalidation', 'select', array(
            'label'  => Mage::helper('webforms')->__('Enable advanced e-mail address validation(experimental)'),
            'title'  => Mage::helper('webforms')->__('Enable advanced e-mail address validation(experimental)'),
            'name'   => 'email_smtpvalidation',
            'note'   => Mage::helper('webforms')->__('Validate Text / E-mail fields through SMTP connection to the mail server. It may slow down form submission process'),
            'values' => Mage::getModel('webforms/webforms_smtpvalidation')->toOptionArray(),
        ));

        $fieldset = $form->addFieldset('admin_notification', array(
            'legend' => Mage::helper('webforms')->__('Admin Notification')
        ));

        $send_email = $fieldset->addField('send_email', 'select', array(
            'label'    => Mage::helper('webforms')->__('Enable admin notification'),
            'title'    => Mage::helper('webforms')->__('Enable admin notification'),
            'name'     => 'send_email',
            'required' => false,
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'note'     => Mage::helper('webforms')->__('Send new results by e-mail. If you have Select/Contact field in the form, e-mail notification will be sent twice: to admin and to selected contact')
        ));

        $template = $fieldset->addField('email_template_id', 'select', array(
            'label'    => Mage::helper('webforms')->__('Admin notification template'),
            'title'    => Mage::helper('webforms')->__('Admin notification template'),
            'name'     => 'email_template_id',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('E-mail template for admin notification letters'),
            'values'   => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $email = $fieldset->addField('email', 'text', array(
            'label' => Mage::helper('webforms')->__('Notification e-mail address'),
            'note'  => Mage::helper('webforms')->__('If empty default notification e-mail address will be used. You can set multiple addresses comma-separated'),
            'name'  => 'email'
        ));

        $attachments_admin = $fieldset->addField('email_attachments_admin', 'select', array(
            'label'  => Mage::helper('webforms')->__('Attach files to notification for admin'),
            'note'  => Mage::helper('webforms')->__('Attach uploaded files to admin notification e-mail'),
            'name'   => 'email_attachments_admin',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $fieldset = $form->addFieldset('countrybased_notifications', array(
            'legend' => Mage::helper('webforms')->__('Countrybased notifications')
        ));

        $country_1 = $fieldset->addField('countrybased_pairs', 'textarea', array(
            'label' => Mage::helper('webforms')->__('Pairs'),
            'note'  => Mage::helper('webforms')->__('Input pair of COUNTRYCODE:email@address on a new line. If there is no match the default admin notification email will be used'),
            'name'  => 'countrybased_pairs'
        ));

        $fieldset = $form->addFieldset('customer_notification', array(
            'legend' => Mage::helper('webforms')->__('Customer Notifications')
        ));

        $duplicate_email = $fieldset->addField('duplicate_email', 'select', array(
            'label'    => Mage::helper('webforms')->__('Enable customer notification'),
            'title'    => Mage::helper('webforms')->__('Enable customer notification'),
            'note'     => Mage::helper('webforms')->__('Send customer notification email.'),
            'name'     => 'duplicate_email',
            'required' => false,
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $customer_template = $fieldset->addField('email_customer_template_id', 'select', array(
            'label'    => Mage::helper('webforms')->__('Customer notification template'),
            'title'    => Mage::helper('webforms')->__('Customer notification template'),
            'name'     => 'email_customer_template_id',
            'required' => false,
            'note'     => Mage::helper('webforms')->__('E-mail template for customers notification letters'),
            'values'   => Mage::getModel('webforms/webforms')->getTemplatesOptions(),
        ));

        $reply_to = $fieldset->addField('email_reply_to', 'text', array(
            'label' => Mage::helper('webforms')->__('Reply-to address for customer'),
            'note'  => Mage::helper('webforms')->__('Set reply-to parameter in customer notifications'),
            'name'  => 'email_reply_to'
        ));

        $attachments_customer = $fieldset->addField('email_attachments_customer', 'select', array(
            'label'  => Mage::helper('webforms')->__('Attach files to notification for customer'),
            'note'  => Mage::helper('webforms')->__('Attach uploaded files to customer notification e-mail'),
            'name'   => 'email_attachments_customer',
            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
        ));

        $this->setChild('form_after', $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence','form_email_dependence')
            ->addFieldMap($duplicate_email->getHtmlId(), $duplicate_email->getName())
            ->addFieldMap($customer_template->getHtmlId(), $customer_template->getName())
            ->addFieldMap($reply_to->getHtmlId(), $reply_to->getName())
            ->addFieldMap($attachments_customer->getHtmlId(), $attachments_customer->getName())
            ->addFieldMap($send_email->getHtmlId(), $send_email->getName())
            ->addFieldMap($email->getHtmlId(), $email->getName())
            ->addFieldMap($template->getHtmlId(), $template->getName())
            ->addFieldMap($attachments_admin->getHtmlId(), $attachments_admin->getName())
            ->addFieldDependence(
                $customer_template->getName(),
                $duplicate_email->getName(),
                1
            )
            ->addFieldDependence(
                $reply_to->getName(),
                $duplicate_email->getName(),
                1
            )
            ->addFieldDependence(
                $attachments_customer->getName(),
                $duplicate_email->getName(),
                1
            )
            ->addFieldDependence(
                $email->getName(),
                $send_email->getName(),
                1
            )
            ->addFieldDependence(
                $template->getName(),
                $send_email->getName(),
                1
            )
            ->addFieldDependence(
                $attachments_admin->getName(),
                $send_email->getName(),
                1
            )
        );


        Mage::dispatchEvent('webforms_adminhtml_webforms_edit_tab_email_prepare_form', array('form' => $form, 'fieldset' => $fieldset));

        if (!Mage::registry('webforms_data')->getId()) {
            Mage::registry('webforms_data')->setData('send_email', 1);
        }

        if (Mage::getSingleton('adminhtml/session')->getWebFormsData()) {
            $form->setValues(Mage::getSingleton('adminhtml/session')->getWebFormsData());
            Mage::getSingleton('adminhtml/session')->setWebFormsData(null);
        } elseif (Mage::registry('webforms_data')) {
            $form->setValues(Mage::registry('webforms_data')->getData());
        }

        return parent::_prepareForm();
    }
}