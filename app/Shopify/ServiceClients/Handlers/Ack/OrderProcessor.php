<?php

namespace App\Shopify\ServiceClients\Handlers\Ack;

/**
 * Class UpdateOrder
 * @package App\Shopify\ServiceClients\Handlers\Ack
 *
 * @category WMG
 * @package  WMG
 * @author   Dinesh Haria <dinesh.haria@wmg.com>
 * @license  WMG License 2021
 * @link     http://www.wmg.com
 */
class OrderProcessor
{
    private const REASON_NOTE_FORMAT = "/Reason: \[.*\]/";
    private const REASON_NOTE_ITEM_FORMAT = "/\[.*\]/";

    private const ACK_NOTE_FORMAT = "/Received ack from warehouse for: \[.*\]/";
    private const ACK_NOTE_ITEM_FORMAT = "/\[.*\]/";

    private const ORDER_STATUS_FIELD = 'tags';
    private const ORDER_COMMENT_FIELD = 'note';

    /**
     * processData
     * @param array $existingShopifyOrder
     * @param array $acknowledgements
     * @return array
     */
    public function processData(array $existingShopifyOrder, array $acknowledgements): array
    {
        foreach ($acknowledgements as $field => $data) {
            if (empty($data)) {
                unset($acknowledgements[$field]);
                continue;
            }

            switch ($field) {
                case self::ORDER_STATUS_FIELD:
                    $acknowledgements[$field] = $this->handleTags($existingShopifyOrder, $data);
                    break;
                case self::ORDER_COMMENT_FIELD:
                    $acknowledgements[$field] = $this->handleNote($existingShopifyOrder, $data);
                    break;
                default:
                    break;
            }

            if (empty($acknowledgements[$field])) {
                unset($acknowledgements[$field]);
            }
        }

        return $acknowledgements;
    }

    /**
     * handleTags
     * @param array $existingShopifyOrder
     * @param string $tags
     * @return string
     */
    protected function handleTags(array $existingShopifyOrder, string $tags): string
    {
        if (isset($existingShopifyOrder[self::ORDER_STATUS_FIELD])
            && !empty($existingShopifyOrder[self::ORDER_STATUS_FIELD])
        ) {
            $existingTags =
                array_map('trim', explode(',', $existingShopifyOrder[self::ORDER_STATUS_FIELD]));
            $newTags = array_map('trim', explode(',', $tags));
            $addTags = array_diff($newTags, $existingTags);

            if (empty($addTags)) {
                return '';
            }

            $tags = array_merge($existingTags, $addTags);
        }

        if (is_array($tags)) {
            $tags = implode(',', $tags);
        }

        return $tags;
    }

    /**
     * handleNote
     * @param array $existingShopifyOrder
     * @param array $newNote
     * @return string|null
     */
    protected function handleNote(array $existingShopifyOrder, array $newNote): ?string
    {
        if (!isset($existingShopifyOrder[self::ORDER_COMMENT_FIELD])
            || empty($existingShopifyOrder[self::ORDER_COMMENT_FIELD])) {
            $formattedReasonLines = $this->formatNoteReasons($newNote);
            $formattedAckLines = $this->formatNoteAck($newNote);
            return $this->formatNote($formattedReasonLines, $formattedAckLines);
        }

        //combine any existing reason code messages with new ones
        $formattedReasonLines = $this->processReasonNote($existingShopifyOrder, $newNote);
        $formattedAckLines = $this->processAckNote($existingShopifyOrder, $newNote);
        return $this->formatNote($formattedReasonLines, $formattedAckLines);
    }

    /**
     * formatReasonNote
     * @param string $formattedReasonLines
     * @param string $formattedAckLines
     * @return string
     */
    protected function formatNote(string $formattedReasonLines, string $formattedAckLines): string
    {
        return trim(sprintf("%s %s", $formattedReasonLines, $formattedAckLines));
    }

    protected function formatNoteReasons(array $newNote): string
    {
        $reasonLines = '';

        if (!empty($newNote['reasonLines'])) {
            $message = implode('|', $newNote['reasonLines']);
            $reasonLines = sprintf("Reason: [%s]", $message);
        }

        return trim($reasonLines);
    }

    protected function formatNoteAck(array $newNote): string
    {
        $acknowledgedItems = '';

        if (!empty($newNote['ackLines'])) {
            $message = implode('|', $newNote['ackLines']);
            $acknowledgedItems = sprintf("Received ack from warehouse for: [%s]", $message);
        }
        return trim($acknowledgedItems);
    }


    /**
     * processReasonNote
     * @param array $existingShopifyOrder
     * @param array $newNote
     * @return string|null
     */
    protected function processReasonNote(array $existingShopifyOrder, array $newNote): ?string
    {
        if (empty($newNote['reasonLines'])) {
            return '';
        }

        $existingNote = $existingShopifyOrder[self::ORDER_COMMENT_FIELD];

        //Check if there are already reasons attached to the shopify order
        preg_match(self::REASON_NOTE_FORMAT, $existingNote, $existingReasonsNotes);

        if (empty($existingReasonsNotes)) {
            $reasonNote = $this->formatNoteReasons($newNote);
            return sprintf("%s %s", $existingNote, $reasonNote);
        }

        //extract existing reasons from the note field
        preg_match(self::REASON_NOTE_ITEM_FORMAT, $existingReasonsNotes[0], $existingReasons);

        //only append new reasons
        if (!empty($existingReasons)) {
            $existingReasons = explode('|', trim($existingReasons[0], '[]'));
            $onlyNewReasons = array_diff($newNote['reasonLines'], $existingReasons);

            if (!empty($onlyNewReasons)) {
                $reasons = array();
                $reasons['reasonLines'] = array_merge($existingReasons, $onlyNewReasons);
                $reasonNote = $this->formatNoteReasons($reasons);

                //Append reason message to note field
                return preg_replace(self::REASON_NOTE_FORMAT, $reasonNote, $existingNote);
            }
        }

        return '';
    }

    /**
     * processReasonNote
     * @param array $existingShopifyOrder
     * @param array $newNote
     * @return string|null
     */
    protected function processAckNote(array $existingShopifyOrder, array $newNote): ?string
    {
        if (empty($newNote['ackLines'])) {
            return '';
        }

        $existingNote = $existingShopifyOrder[self::ORDER_COMMENT_FIELD];

        //Check if there are already reasons attached to the shopify order
        preg_match(self::ACK_NOTE_FORMAT, $existingNote, $existingAckNotes);

        if (empty($existingAckNotes)) {
            $ackNote = $this->formatNoteAck($newNote);
            return sprintf("%s %s", $existingNote, $ackNote);
        }

        //extract existing reasons from the note field
        preg_match(self::ACK_NOTE_ITEM_FORMAT, $existingAckNotes[0], $existingAcks);

        //only append new reasons
        if (!empty($existingAcks)) {
            $existingAcks = explode('|', trim($existingAcks[0], '[]'));
            $onlyNewAcks = array_diff($newNote['ackLines'], $existingAcks);

            if (!empty($onlyNewAcks)) {
                $acks = array();
                $acks['ackLines'] = array_merge($existingAcks, $onlyNewAcks);
                $ackNote = $this->formatNoteAck($acks);

                //Append reason message to note field
                return preg_replace(self::ACK_NOTE_FORMAT, $ackNote, $existingNote);
            }
        }

        return '';
    }
}
