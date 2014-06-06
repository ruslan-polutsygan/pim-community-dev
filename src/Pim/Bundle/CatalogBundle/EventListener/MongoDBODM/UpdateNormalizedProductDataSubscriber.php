<?php

namespace Pim\Bundle\CatalogBundle\EventListener\MongoDBODM;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pim\Bundle\CatalogBundle\Entity\AttributeOptionValue;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;

/**
 * Sets the normalized data of a Product document when related entities are modified
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateNormalizedProductDataSubscriber implements EventSubscriber
{
    /** @var ManagerRegistry */
    protected $registry;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var string */
    protected $productClass;

    /** @var ChannelManager */
    protected $channelManager;

    /** 
     * Actions to be executed following changes on Entities
     *
     */
    protected $pendingActions = array();

    /**
     * @param ManagerRegistry     $registry
     * @param NormalizerInterface $normalizer
     * @param string              $productClass
     * @param ChannelManager      $channelManager
     */
    public function __construct(ManagerRegistry $registry, NormalizerInterface $normalizer, $productClass, ChannelManager $channelManager)
    {
        $this->registry       = $registry;
        $this->normalizer     = $normalizer;
        $this->productClass   = $productClass;
        $this->channelManager = $channelManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return ['onFlush', 'postFlush'];
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $uow = $args->getEntityManager()->getUnitOfWork();

        foreach ($uow->getScheduledEntityUpdates() as $entity) {
            error_log("DEBUG entity in scheduledEntityUpdates:".get_class($entity));
            if ($entity instanceof AttributeOptionValue) {
                $this->scheduleForAttributeOptionValue();
            }

            if ($entity instanceof Family) {
                //TODO :  check if family labels have changed and schedule a massive update
                //on family label
                // Priority 1
            }
            if ($entity instanceof Channel) {
                //TODO :  check if a locale has been removed from the locale list
                //on family label and remove normalizedData
                // Priority 2
            }
            
        }

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            error_log("DEBUG entity in scheduledEntityUpdates:".get_class($entity));
            if ($entity instanceof AttributeOptionValue) {
                $this->scheduleForAttributeOptionValue();
            }
        }

        foreach ($uow->getScheduledEntityDeletions() as $entity) {
            error_log("DEBUG entity in scheduledEntityDeletions:".get_class($entity));
            if ($entity instanceof AttributeOptionValue) {
                $this->scheduleForAttributeOptionValue();
            }
            if ($entity instanceof Family) {
                //TODO :  check if family and remove family from linked products
                // Priority 1
            }
            if ($entity instanceof Channel) {
                //TODO : cleanup normalizedData
                // Priority 2
            }
            if ($entity instanceof AttributeOption) {
                //TODO : cleanup normalizedData
                // Priority 2
            }
        }
    }

    /**
     * Schedule actions to apply on the products when an AttributeOptionValue
     * has been changed
     *
     * @param AttributeOptionValue $attributeOptionValue
     */
    protected function scheduleForAttributeOptionValue(AttributeOptionValue $optionValue)
    {
        $attribute = $optionValue->getAttributeOption()->getAttribute();
        $fieldNames = $this->getFieldNames($attribute);

        $actions = array();

        if ('options' === $attribute->getBackendType()) {
            $actions = $this->getActionsForMultiOptions($optionValue, $fieldNames);
        } else {
            $actions = $this->getActionsForUniqueOption($optionValue, $fieldNames);
        }
        $this->pendingActions = $actions + $this->pendingActions;
    }

    /**
     * Get actions fo

    /**
     * Generates all field names variations from attribute code
     * and locale and channel
     *
     * @param AbstractAttribute
     *
     * @return array
     */
    protected function getFieldNames(AbstractAttribute $attribute)
    {
        $fieldNames = array();

        foreach($this->getChannels() as $channel) {
            foreach($channel->getLocales() as $locale) {
                $fieldNames[] = $this->getFieldName($attribute, $channel, $locale);
            }
        }

        return array_unique($fieldNames);
    }

    /**                                                                                             
     * Get the field name for normalized data
     *
     * @param AbstractAttribute $attribute
     * @param Channel           $channel
     * @param Locale            $locale
     *
     * @return string
     */
    protected function getFieldName(AbstractAttribute $attribute, Channel $channel, Locale $locale)
    {
        $suffix = '';

        if ($attribute->isLocalizable()) {
            $suffix = sprintf('-%s', $locale->getCode());
        }
        if ($attribute->isScopable()) {
            $suffix .= sprintf('-%s', $channel->getCode());
        }

        return $attribute->getCode() . $suffix;
    }
     


    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->executePendingActions();
    }

    /**
     * Execute pending actions
     */
    public function executePendingActions()
    {
        //TODO ...
    }

}
