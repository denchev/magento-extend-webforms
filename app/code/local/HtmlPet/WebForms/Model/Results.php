<?php

/**
 * @author         Vladimir Popov
 * @copyright      Copyright (c) 2014 Vladimir Popov
 */
class HtmlPet_WebForms_Model_Results
    extends VladimirPopov_WebForms_Model_Results
{
    private function getCountryBasedEmail($webform)
    {

        $pairs = $webform->getData('countrybased_pairs');

        $country_support_email = array();
        $pairs_split = preg_split("/\n|\r\n/", $pairs);

        foreach($pairs_split as $row) {
            $pair = explode(':', $row);

            $country_support_email[trim($pair[0])] = trim($pair[1]);
        }

        $country_code = $this->getValue('country');

        if(isset($country_support_email[$country_code])) {

            return $country_support_email[$country_code];
        }

        return null;
    }

    public function sendEmail($recipient = 'admin', $contact = false)
    {

        $webform = Mage::getModel('webforms/webforms')
            ->setStoreId($this->getStoreId())
            ->load($this->getWebformId());
        if (!Mage::registry('webform'))
            Mage::register('webform', $webform);

        $emailSettings = $webform->getEmailSettings();

        // for admin
        $sender = Array(
            'name' => $this->getCustomerName(),
            'email' => $this->getReplyTo($recipient),
        );

        if (!$sender['name']) {
            $sender['name'] = $sender['email'];
        }

        // for customer
        if ($recipient == 'customer') {
            $sender['name'] = Mage::app()->getStore($this->getStoreId())->getFrontendName();
            $contact_array = $this->getContactArray();

            // send letter from selected contact
            if ($contact_array) {
                $sender = $contact_array;
            }
        }

        if (Mage::getStoreConfig('webforms/email/email_from')) {
            $sender['email'] = Mage::getStoreConfig('webforms/email/email_from');
        }

        $subject = $this->getEmailSubject($recipient);

        $email = $emailSettings['email'];

        // Send an email to an appropriate recipient based on the customer selected country
        if($country_based_email = $this->getCountryBasedEmail($webform)) {
            $email = $country_based_email;
        }

        //for customer
        if ($recipient == 'customer') {
            $email = $this->getCustomerEmail();
        }

        $name = Mage::app()->getStore($this->getStoreId())->getFrontendName();

        if ($recipient == 'customer') {
            $name = $this->getCustomerName();
        }

        if ($recipient == 'contact') {
            if (empty($contact['email'])) return false;
            $email = $contact['email'];
            $name = $contact['name'];
            $recipient = 'admin';
        }

        $webformObject = new Varien_Object();
        $webformObject->setData($webform->getData());

        $store_group = Mage::app()->getStore($this->getStoreId())->getFrontendName();
        $store_name = Mage::app()->getStore($this->getStoreId())->getName();

        $vars = Array(
            'webform_subject' => $subject,
            'webform_name' => $webform->getName(),
            'webform_result' => $this->toHtml($recipient),
            'customer_name' => $this->getCustomerName(),
            'customer_email' => $this->getCustomerEmail(),
            'ip' => $this->getIp(),
            'store_group' => $store_group,
            'store_name' => $store_name,
            'result' => $this->getTemplateResultVar(),
            'webform' => $webformObject,
            'timestamp' => Mage::helper('core')->formatDate($this->getCreatedTime(), 'medium', true),
        );

        $customer = $this->getCustomer();

        if ($customer) {
            $customerObject = new Varien_Object();
            $customerObject->setData($customer->getData());
            $vars['customer'] = $customerObject;
        }

        $post = Mage::app()->getRequest()->getPost();

        if ($post) {
            $postObject = new Varien_Object();
            $postObject->setData($post);

            // set region name if found
            if (!empty($post['region_id'])) {
                $postObject->setData('region_name', $post['region_id']);
                $region_name = Mage::getModel('directory/region')->load($post['region_id'])->getName();
                if ($region_name) {
                    $postObject->setData('region_name', $region_name);
                }
            }
            $vars['data'] = $postObject;
        }

        $vars['noreply'] = Mage::helper('webforms')->__('Please, don`t reply to this e-mail!');

        $storeId = $this->getStoreId();
        $templateId = 'webforms_notification';
        if ($webform->getEmailTemplateId()) {
            $templateId = $webform->getEmailTemplateId();
        }
        if ($recipient == 'customer') {
            if ($webform->getEmailCustomerTemplateId()) {
                $templateId = $webform->getEmailCustomerTemplateId();
            }
        }
        $file_list = $this->getFiles();
        $send_multiple_admin = false;
        if (is_string($email)) {
            if ($recipient == 'admin' && strstr($email, ','))
                $send_multiple_admin = true;
        }

        if ($send_multiple_admin) {
            $email_array = explode(',', $email);
            foreach ($email_array as $email) {

                $mail = Mage::getModel('core/email_template')
                    ->setTemplateSubject($subject)
                    ->setReplyTo($this->getReplyTo($recipient));

                //file content is attached
                if ($webform->getEmailAttachmentsAdmin())
                    foreach ($file_list as $file) {
                        $path = $file['path'];
                        $attachment = file_get_contents($path);
                        $mail->getMail()->createAttachment(
                            $attachment,
                            Zend_Mime::TYPE_OCTETSTREAM,
                            Zend_Mime::DISPOSITION_ATTACHMENT,
                            Zend_Mime::ENCODING_BASE64,
                            $file['name']
                        );
                    }

                //attach pdf version to email
                if ($webform->getPrintAttachToEmail()) {
                    require_once(Mage::getBaseDir('lib') . '/Webforms/mpdf/mpdf.php');
                    $mpdf = new mPDF('utf-8', 'A4');
                    $mpdf->WriteHTML($this->toPrintableHtml());

                    $mail->getMail()->createAttachment(
                        $mpdf->Output('', 'S'),
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        'result' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s', $this->getCreatedTime()) . '.pdf'
                    );
                }

                $mail->sendTransactional($templateId, $sender, trim($email), $name, $vars, $storeId);

            }
        } else {
            $mail = Mage::getModel('core/email_template')
                ->setTemplateSubject($subject)
                ->setReplyTo($this->getReplyTo($recipient));
            //file content is attached
            if (($webform->getEmailAttachmentsAdmin() && $recipient == 'admin') || ($webform->getEmailAttachmentsCustomer() && $recipient == 'customer'))
                foreach ($file_list as $file) {
                    $path = $file['path'];
                    $attachment = file_get_contents($path);
                    $mail->getMail()->createAttachment(
                        $attachment,
                        Zend_Mime::TYPE_OCTETSTREAM,
                        Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_BASE64,
                        $file['name']
                    );
                }

            //attach pdf version to email
            if (($webform->getPrintAttachToEmail() && $recipient == 'admin') || ($webform->getCustomerPrintAttachToEmail() && $recipient == 'customer')) {
                require_once(Mage::getBaseDir('lib') . '/Webforms/mpdf/mpdf.php');
                $mpdf = new mPDF('utf-8', 'A4');
                $mpdf->WriteHTML($this->toPrintableHtml($recipient));

                $mail->getMail()->createAttachment(
                    $mpdf->Output('', 'S'),
                    Zend_Mime::TYPE_OCTETSTREAM,
                    Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_BASE64,
                    'result' . Mage::getSingleton('core/date')->date('Y-m-d_H-i-s', $this->getCreatedTime()) . '.pdf'
                );
            }
            $mail->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
            return $mail->getSentSuccess();
        }
    }
}

