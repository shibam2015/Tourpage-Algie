<?php

/*
 * Copyright (C) 2016 amit
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Tourpage\Library;

use Phalcon\Mvc\User\Component;

require_once __DIR__ . '/Swift/swift_required.php';

/**
 * Mail library
 * @author amit
 */
class Mail extends Component {

    /**
     * SMTP Server Host
     * @var string
     */
    private $smtpHost = '';

    /**
     * SMTP Server Port
     * @var int
     */
    private $smtpPort = 0;

    /**
     * SMTP Server Security Type
     * @var string
     */
    private $smtpSecurity = 'ssl';

    /**
     * SMTP Server Authenticate Username
     * @var mixed
     */
    private $smtpUsername = '';

    /**
     * SMTP Server Authenticate Password
     * @var mixed 
     */
    private $smtpPassword = '';

    /**
     * Wheather to use SMTP protocol or will 
     * go for nativ mail() function
     * @var bool
     */
    private $isSmtp = TRUE;

    /**
     * Send Mail From Address
     * @example "some-email@example.com" => "Jhon Doe"
     * @var array
     */
    private $from = array();

    /**
     * Sender Mail
     * @var array
     */
    private $to = array();

    /**
     * CC address
     * @var array
     */
    private $cc = array();

    /**
     * BCC Address
     * @var array
     */
    private $bcc = array();

    /**
     * Reply-To address
     * @var array
     */
    private $replyTo = array();

    /**
     * Mail Subject
     * @var string
     */
    private $subject = null;

    /**
     * Mail Main Body Content
     * @var string
     */
    private $body = null;

    /**
     * Mail Attachments
     * @var array
     */
    private $attachments = [];

    /**
     * Dynamic attachments
     * like dynamicaly created pdf file
     * @var array
     */
    private $dynamicAttachments = [];

    /**
     * Message Object
     * @see Swift_Message
     * @var object
     */
    private $message = null;

    /**
     * SMTP Server Connection Object
     * @see Swift_SmtpTransport
     * @var object 
     */
    private $connection = null;

    /**
     * Send Mail Mailer Object
     * @see Swift_Mailer
     * @var object
     */
    private $mailer = null;

    /**
     * SMTP Connection
     */
    private function connect() {
        if (!$this->connection) {
            if ($this->isSmtp()) {
                $this->smtpHost = \Tourpage\Helpers\Utils::getVar('smtp_host');
                $this->smtpUsername = \Tourpage\Helpers\Utils::getVar('smtp_user');
                $this->smtpPassword = \Tourpage\Helpers\Utils::getVar('smtp_pass');
                $this->smtpPort = \Tourpage\Helpers\Utils::getVar('smtp_port');

                $this->connection = \Swift_SmtpTransport::newInstance($this->smtpHost, $this->smtpPort, $this->smtpSecurity)
                        ->setUsername($this->smtpUsername)
                        ->setPassword($this->smtpPassword);
            } else {
                $this->connection = \Swift_MailTransport::newInstance();
            }
        }
    }

    /**
     * Getting SMTP connection object
     * @return type
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * TRUE to use SMTP or not
     * @return bool
     */
    public function isSmtp() {
        return $this->isSmtp;
    }

    /**
     * Change SMTP behaviour
     * @param bool $smtp
     */
    public function setIsSmtp($smtp) {
        $this->isSmtp = $smtp;
    }

    /**
     * Setting To Address
     * @param string $address
     * @param string $name optional
     */
    public function setTo($address, $name = NULL) {
        if ($name == NULL) {
            $this->to[] = $address;
        } else {
            $this->to[$address] = $name;
        }
    }

    /**
     * Getting To Address
     * @return array
     */
    public function getTo() {
        return $this->to;
    }

    /**
     * Setting From Address
     * @param string $address
     * @param string $name optional
     */
    public function setFrom($address, $name = NULL) {
        if ($name == NULL) {
            $this->from[] = $address;
        } else {
            $this->from[$address] = $name;
        }
    }

    /**
     * Getting From Address
     * @return array
     */
    public function getFrom() {
        if (count($this->from) <= 0) {
            $this->from['no-reply@tourpage.net'] = 'Tourpage Team';
        }
        return $this->from;
    }

    /**
     * Setting CC address
     * @param string $address
     * @param string $name optional
     */
    public function setCc($address, $name = NULL) {
        if ($name == NULL) {
            $this->cc[] = $address;
        } else {
            $this->cc[$address] = $name;
        }
    }

    /**
     * Getting CC address
     * @return array
     */
    public function getCc() {
        return $this->cc;
    }

    /**
     * Setting BCC address
     * @param string $address
     * @param string $name optional
     */
    public function setBcc($address, $name = NULL) {
        if ($name == NULL) {
            $this->bcc[] = $address;
        } else {
            $this->bcc[$address] = $name;
        }
    }

    /**
     * Getting BCC address
     * @return array
     */
    public function getBcc() {
        return $this->bcc;
    }

    /**
     * Setting Reply-To address
     * @param string $address
     * @param string $name optional
     */
    public function setReplyTo($address, $name = NULL) {
        if ($name == NULL) {
            $this->replyTo[] = $address;
        } else {
            $this->replyTo[$address] = $name;
        }
    }

    /**
     * Getting Reply-To address
     * @return array
     */
    public function getReplyTo() {
        return $this->replyTo;
    }

    /**
     * Setting Mail Subject
     * @param string $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * Getting Mail Subject
     * @return string
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * Setting Mail Main Body
     * @param mix $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * Getting Mail Main Body
     * @return mix
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * Adding or Setting Mail Attachment
     * @param string $fileName
     */
    public function setAttachment($fileName) {
        $this->attachments[] = $fileName;
    }

    /**
     * Adding dynamic mail attachment
     * like dynamicaly created pdf file
     * @param string $fileName
     * @param string $fileData
     * @param string $fileType
     */
    public function setDynamicAttachment($fileName, $fileData = null, $fileType = '') {
        $this->dynamicAttachments[] = [
            'name' => $fileName,
            'data' => $fileData,
            'type' => $fileType
        ];
    }

    /**
     * Building Mail body with from, to, subject, body and attachment
     */
    private function _buildMessage() {
        $this->message = \Swift_Message::newInstance();
        if (count($this->getFrom()) > 1) {
            $this->message->setFrom($this->getFrom());
        } else {
            $this->message->setSender($this->getFrom());
        }
        $this->message->setTo($this->getTo());

        if (count($this->getCc()) > 0) {
            $this->message->setCc($this->getCc());
        }
        if (count($this->getBcc()) > 0) {
            $this->message->setBcc($this->getBcc());
        }
        if (count($this->getReplyTo()) > 0) {
            $this->message->setReplyTo($this->getReplyTo());
        }
        $this->message->setSubject($this->getSubject());
        $this->message->setBody($this->getBody());
        $this->message->setContentType('text/html');

        if (count($this->attachments) > 0) {
            foreach ($this->attachments as $attachFile) {
                $this->message->attach(\Swift_Attachment::fromPath($attachFile)->setDisposition('inline'));
            }
        }
        if (count($this->dynamicAttachments) > 0) {
            foreach ($this->dynamicAttachments as $attachFile) {
                $this->message->attach(\Swift_Attachment::newInstance($attachFile['data'], $attachFile['name'], $attachFile['type']));
            }
        }
    }

    /**
     * Sending Mail
     * @return bool
     */
    public function send() {
        $this->_buildMessage();
        $this->connect();
        $this->mailer = \Swift_Mailer::newInstance($this->connection);
        if (count($this->getTo()) > 0) {
            $this->mailer->send($this->message);
        }
        return true;
    }

}
