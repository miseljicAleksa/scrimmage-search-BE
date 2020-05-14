<?php

namespace Workmark\Mailer;


    /**
     * The Mailer class
     *
     * Unhealthy references:
     *  sender and subject in expressMail
     *     transport host in setTransport
     *     APPLICATION_ENV in constructor during setTransport
     *     Better field names in DB (plain_content, html_content ...), to be changed in all references (prepareEmail)
     *
     * @category      workmark
     * @package       Mailer
     * @license       BSD License
     * @version       0.01
     * @since         2012-06-19
     * @author        Arsen <workmark@workmark.me>
     */
    class Mailer
    {

        /**
         * @see setDeveloperRecipient and sendMail
         * @var string
         */
        protected $sDeveloperRecipient = false;

        /**
         * Will contain the default sender and subject
         *
         * @var array
         */
        protected $aDefaults = array();

        /**
         * How many emails to take from the queue and send
         *
         * @var int
         */
        protected $iQueueRange;

        /**
         * The transport to be used for sending
         *
         * @var \Swift_Transport
         */
        protected $oTransport;

        /**
         * Swift Mailer object
         *
         * @var \Swift_Mailer
         */
        protected $oMailer;

        /**
         * The array holding $emails - each email is an object in itself, with all required data.
         *
         * @var array
         */
        protected $aPreparedEmails;

        /**
         * Array of failed recipients per sent email.
         * This is an assoc. array where keys are email IDs, while values are arrays of failed recipients per sent email
         *
         * @var array
         */
        protected $aFailedRecipients;

        /**
         * Number of queued emails during last queueing
         *
         * @var int
         */
        protected $iQueued = 0;

        /**
         * The Service that allows the mailer to have a database connection
         *
         * @var null
         */
        protected $oService = null;

        /**
         * The location of static attachments
         *
         * @var string
         */
        protected $sStaticAttachmentsFolder;

        /**
         * Whether or not to send to the archive inbox as well, in the format of a BCC email
         *
         * @var bool
         */
        protected $bDefaultBccActive = true;

        /**
         * Whether or not to archive the sent emails
         *
         * @var null
         */
        protected $bArchiving = null;

        /**
         * Number of successfully sent emails
         *
         * @var int
         */
        protected $iSent = 0;

        public function __construct()
        {
        	//require ROOT_PATH .'/vendor/swiftmailer/swiftmailer/lib/swift_required.php';
        	//require ROOT_PATH .'/vendor/swiftmailer/swiftmailer/lib/classes/Swift/DependencyContainer.php';
        	//require ROOT_PATH .'/vendor/swiftmailer/swiftmailer/lib/classes/Swift/Preferences.php';
        	
        	
        	
        	
        	
        	
        	$this->aPreparedEmails = array();

            $this->aDefaults['from']    = 'support@scrimmagesearch.com';
            $this->aDefaults['subject'] = 'Scrimmage Search Notification Message';
			
            // Set up defaults
            $this->bDefaultBccActive = false;
            $this->setQueueRange();
            $this->setTransport((defined('APPLICATION_ENV') && constant('APPLICATION_ENV') == 'production') ? 3 : 3);//2 : 3

            $this->oMailer = new \Swift_Mailer($this->getTransport());
        }

        /**
         * Sets the number of queued emails. Automatic when queuePreparedEmails is called.
         *
         * @param $iQueued
         *
         * @return Mailer
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        protected function setNumberOfQueued($iQueued)
        {
            $this->iQueued = $iQueued;

            return $this;
        }

        /**
         * Returns the number of queued emails during last queueing.
         *
         * @return int
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function getNumberOfQueued()
        {
            return $this->iQueued;
        }

        /**
         * Sets the queue range of the Mailer instance
         *
         * @param int $iValue
         *
         * @return \workmark\Mailer
         * @throws \Exception
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function setQueueRange($iValue = 900)
        {
            if ($iValue > 0 && $iValue < 1000) {
                $this->iQueueRange = $iValue;
            } else {
                throw new \Exception('Wrong queueRange value given - value must be between 0 and 1000, you gave ' . $iValue);
            }

            return $this;
        }

        /**
         * Sets the developer recipient.
         * Leave default of pass false to deactivate debugging send mode
         * and send to normal recipients. Pass email to define a custom
         * email address which should receive all the emails.
         *
         * @param bool|string $sEmail
         *
         * @return Mailer
         *
         * @since         2012-09-01
         * @author        Arsen <workmark@workmark.me>
         */
        public function setDeveloperRecipient($sEmail = false)
        {
            $this->sDeveloperRecipient = $sEmail;

            return $this;
        }

        /**
         * Returns the defined developer recipient
         * Will be empty string if no developer recipient was set
         *
         * @return string|bool
         *
         * @since         2012-09-01
         * @author        Arsen <workmark@workmark.me>
         */
        public function getDeveloperRecipient()
        {
            return $this->sDeveloperRecipient;
        }

        /**
         * Retrieves the queueRange of the given Mailer instance
         *
         * @return int
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function getQueueRange()
        {
            return $this->iQueueRange;
        }

        /**
         * Returns the number of successfully sent emails
         *
         * @return int
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function getNumberOfSent()
        {
            return $this->iSent;
        }

        /**
         * Sets the transport. Defaults to localhost
         *
         * @param int|\Swift_Transport $mTransport
         *
         * @return \workmark\Mailer
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function setTransport($mTransport = 3)
        {
            if (is_a($mTransport, '\Swift_Transport')) {
                $this->oTransport = $mTransport;
            } else {
                if (is_int($mTransport)) {
                    switch ($mTransport) {
                        default:
                        case 1:
                        	$this->oTransport = \Swift_MailTransport::newInstance();
                            break;
                        case 2:
                            $this->oTransport = new \Swift_SmtpTransport('localhost');
                            break;
                        case 3:
                        	$this->oTransport = (new \Swift_SmtpTransport('smtp.office365.com', 587, 'tls'))
                        	->setUsername('support@scrimmagesearch.com')
                        	->setPassword('Toy13240');
                            break;
                    }
                } else {
                    $this->oTransport = new \Swift_SmtpTransport($mTransport);
                }
            }
            $this->oMailer = \Swift_Mailer::newInstance($this->getTransport());

            return $this;
        }

        /**
         * Returns the transport in use on the current Mailer interface
         *
         * @return \Swift_Transport
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function getTransport()
        {
            return $this->oTransport;
        }

        /**
         * Sends a prepared message
         *
         * @param \Swift_Message $oMessage
         *
         * @return int Number of recipients accepted for delivery
         * @throws \Exception
         *
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        protected function sendMail(\Swift_Message $oMessage)
        {
        	//$oMessage->setReturnPath('mts-mdn@workmark.com');
            if ($this->getDeveloperRecipient() !== false) {
                $sEmail = filter_var($this->getDeveloperRecipient(), FILTER_VALIDATE_EMAIL);
                if (!empty($sEmail)) {
                    $oMessage->setTo($sEmail);
                } else {
                	throw new \Exception('Developer recipient is set but invalid. Sending will not happen');
                }
            }
            
            //$oMessage->setCc(array('arsen.leontijevic@gmail.com'));
            
            // Debugging hardcoded protection mode
            /** @todo Remove in production!!!
            if (!isset($_GET['trueSending']) || (isset($_GET['trueSending']) && $_GET['trueSending'] != 'yes')) {
                $oMessage->setTo('mail-archive@workmarkweb.org');
                $oMessage->setCc(array('leontijevic@workmarkopen.com', 'duric@workmarkopen.com'));
            }
            */
			//return true;
            return $this->oMailer->send($oMessage, $this->aFailedRecipients[$oMessage->getId()]);
        }


        /**
         * Returns the array of failed recipients. This is an assoc. array which has message IDs as keys and an array of failed recipients per given email as the value
         *
         * @param mixed $id The message id. If provided, only fails for the given email are retrieved, otherwise, everything is retrieved.
         *
         * @return array
         *
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function getFailedRecipients($id = 0)
        {
            if ($id) {
                return $this->aFailedRecipients[$id];
            } else {
                return $this->aFailedRecipients;
            }
        }

        /**
         * Sends and optionally archives the prepared emails
         *
         * @param string $sDevelopmentRecipient Enter custom email address if you want the email sent to this address instead (good for testing)
         *
         * @return Mailer
         * @throws \Exception
         *
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function sendPreparedEmails($sDevelopmentRecipient = null)
        {
            if ($this->hasPreparedEmails()) {

                foreach ($this->getPreparedEmails() as $i => $aEmail) {
                    if ($sDevelopmentRecipient) {
                        $aEmail->setTo($sDevelopmentRecipient);
                    }
                    if ($this->sendMail($aEmail)) {
                        $this->iSent++;
                        unset($this->aPreparedEmails[$i]);
                    } else {
                    	throw new \Exception('Failed to send email.');
                    }
                }
            }

            return $this;
        }

        /**
         * Returns the array of prepared email objects
         *
         * @return array
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function getPreparedEmails()
        {
            return $this->aPreparedEmails;
        }

        /**
         * Returns whether or not there are any prepared emails in the instance
         *
         * @return bool
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function hasPreparedEmails()
        {
            return (bool)(count($this->getPreparedEmails()));
        }

        /**
         * Verifies that all the data required for proper email content has been send.<br />
         * This checks for the body and subject, and throws exceptions if any of those aren't found
         *
         * This method takes a reference to the data array and as such might make some minor changes to
         * it (i.e. inserting an empty array key if the 'signature' key is not present etc)
         *
         * @param array $aData
         *
         * @return boolean|\Exception
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        protected function verifySendableContent(&$aData)
        {
            $e = true;
            if (!isset($aData['body'])) {
            	$e = new \Exception('Content Verification Failed: An email MUST have plain text content.');
            }
            if (!isset($aData['subject'])) {
            	$e = new \Exception('Content Verification Failed: An email MUST have a subject.');
            }
            if (!isset($aData['signature'])) {
                $aData['signature'] = '';
            }

            return $e;
        }

        /**
         * Prepares a new message for sending
         *
         * @param array|string $mTo
         * @param array|string $mFrom
         * @param array        $aData
         * @param array        $aHeaders
         * @param array        $aAttachments
         *
         * @return Mailer
         * @throws bool|\Exception
         * @since         2012-06-19
         * @author        Arsen <workmark@workmark.me>
         */
        public function prepareEmail($mTo, $mFrom, $aData)
        {

            $mVerification = $this->verifySendableContent($aData);
            if ($mVerification === true) {

                $message = \Swift_Message::newInstance();
                $message->setSubject($aData['subject']);
                $message->setBody($aData['body'] . $aData['signature']);

                if (isset($aData['bodyHtml']) && !empty($aData['bodyHtml'])) {
                    $aData['body_html'] = $aData['bodyHtml'];
                }
                if (isset($aData['body_html']) && !empty($aData['body_html'])) {
                    $message->addPart($aData['body_html'], 'text/html');
                }
                
                $message->setTo($mTo);
                $message->setFrom($mFrom);
                

                if ($this->bDefaultBccActive) {
                    //$message->addBcc('mail-archive@workmark.com');
                }

                $this->aPreparedEmails[] = $message;
            } else {
                throw $mVerification;
            }

            if ($this->getNumberOfSent() > 0) {
                $this->iSent = 0;
            }

            return $this;
        }

    }
