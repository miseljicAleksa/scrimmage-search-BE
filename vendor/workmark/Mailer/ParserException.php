<?php

namespace Workmark\Mailer;

    /**
     * The Parser component is used to replace tags from a string
     * of text with actual values provided through a simple associative
     * array. It is used in rendering personalized content for emails,
     * CMS pages and PDF documents, among other things
     *
     */
    class ParserException extends \Exception
    {

        protected $aParams;
        protected $mExtraInfo;

        protected $aTagValues;
        protected $mContent;

        /**
         * Sets any kind of extra information
         *
         * @param mixed $mInfo
         *
         * @return ParserException
         *
         * @since         2012-06-27
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function setExtraInfo($mInfo = array())
        {
            $this->mExtraInfo = $mInfo;
            return $this;
        }

        /**
         * Gets the extra information
         * @return mixed
         *
         * @since         2012-06-27
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function getExtraInfo()
        {
            return $this->mExtraInfo;
        }

        /**
         * Sets passed content
         *
         * @param mixed $mContent
         *
         * @return ParserException
         *
         * @since         2012-06-27
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function setContent($mContent)
        {
            $this->mContent = $mContent;
            return $this;
        }

        /**
         * Gets the passed content
         * @return mixed
         *
         * @since         2012-06-27
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function getContent()
        {
            return $this->mContent;
        }

        /**
         * Sets passed Tag Value pairs
         *
         * @param array $aTagValues
         *
         * @return ParserException
         *
         * @since         2012-06-27
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function setTagValues($aTagValues = array())
        {
            $this->aTagValues = $aTagValues;
            return $this;
        }

        /**
         * Gets passed tag value pairs
         * @return array
         *
         * @since         2012-06-27
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function getTagValues()
        {
            return $this->aTagValues;
        }

        /**
         * Returns the unparsed tags formed into a string
         *
         * @return string
         *
         * @since         2012-09-28
         * @author        Bruno Škvorc <bruno@skvorc.me>
         */
        public function getUnparsedTagsString()
        {
            $sUnparsedTagsString = '';
            $aUnparsedTags = $this->getExtraInfo();
            if (isset($aUnparsedTags['unparsed_tags']) && !empty($aUnparsedTags['unparsed_tags'])) {
                $sUnparsedTagsString .= ': ';
                foreach ($aUnparsedTags['unparsed_tags'] as &$sTag) {
                    $sUnparsedTagsString .= $sTag . ', ';
                }
                $sUnparsedTagsString = trim($sUnparsedTagsString, ', ');
            }
            return $sUnparsedTagsString;
        }
    }
