<?php

namespace Oro\Bundle\IssueBundle\ImportExport\Serializer\Normalizer;

use Doctrine\Common\Collections\Collection;

use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;

use Oro\Bundle\IssueBundle\Entity\Issue;
use Oro\Bundle\UserBundle\Entity\User;

class IssueNormalizer implements NormalizerInterface, DenormalizerInterface, SerializerAwareInterface
{
    const ISSUE_TYPE = 'Oro\Bundle\IssueBundle\Entity\Issue';
    const USER_TYPE  = 'Oro\Bundle\UserBundle\Entity\User';

    static protected $scalarFields = array(
        'id',
        'code',
        'summary',
        'issueType',
        'description',
        'priority',
        'resolution',
        'step',
        'organization',
    );

    /**
     * @var SerializerInterface|NormalizerInterface|DenormalizerInterface
     */
    protected $serializer;

    public function setSerializer(SerializerInterface $serializer)
    {
        if (!$serializer instanceof NormalizerInterface || !$serializer instanceof DenormalizerInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'Serializer must implement "%s" and "%s"',
                    'Symfony\Component\Serializer\Normalizer\NormalizerInterface',
                    'Symfony\Component\Serializer\Normalizer\DenormalizerInterface'
                )
            );
        }
        $this->serializer = $serializer;
    }

    /**
     * @param Issue $object
     * @param mixed $format
     * @param array $context
     * @return array
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $result = $this->getScalarFieldsValues($object);

        $result['owner'] = $this->normalizeObject(
            $object->getOwner(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );
        $result['assignee'] = $this->normalizeObject(
            $object->getAssignee(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );
        $result['reporter'] = $this->normalizeObject(
            $object->getReporter(),
            $format,
            array_merge($context, array('mode' => 'short'))
        );

        return $result;
    }

    /**
     * @param mixed $object
     * @param mixed $format
     * @param array $context
     * @return mixed
     */
    protected function normalizeObject($object, $format = null, array $context = array())
    {
        $result = null;
        if (is_object($object)) {
            $result = $this->serializer->normalize($object, $format, $context);
        }
        return $result;
    }

    /**
     * @param $collection
     * @param mixed $format
     * @param array $context
     * @return array|bool|float|int|null|string
     */
    protected function normalizeCollection($collection, $format = null, array $context = array())
    {
        $result = array();
        if ($collection instanceof Collection && !$collection->isEmpty()) {
            $result = $this->serializer->normalize($collection, $format, $context);
        }
        return $result;
    }

    /**
     * @param Issue $object
     * @return array
     */
    protected function getScalarFieldsValues(Issue $object)
    {
        $result = array();
        foreach (static::$scalarFields as $fieldName) {
            $getter = 'get' .ucfirst($fieldName);
            $result[$fieldName] = $object->$getter();
        }
        return $result;
    }

    /**
     * @param mixed $data
     * @param string $class
     * @param mixed $format
     * @param array $context
     * @return Issue
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $data = is_array($data) ? $data : array();
        $result = new Issue();
        $this->setScalarFieldsValues($result, $data);
        $this->setObjectFieldsValues($result, $data);

        return $result;
    }

    /**
     * @param Issue $object
     * @param array $data
     */
    protected function setScalarFieldsValues(Issue $object, array $data)
    {
        foreach (static::$scalarFields as $fieldName) {
            $setter = 'set' .ucfirst($fieldName);
            if (array_key_exists($fieldName, $data)) {
                $object->$setter($data[$fieldName]);
            }
        }
    }

    /**
     * @param Issue $object
     * @param array $data
     * @param mixed $format
     * @param array $context
     */
    protected function setObjectFieldsValues(Issue $object, array $data, $format = null, array $context = array())
    {
        $this->setNotEmptyValues(
            $object,
            array(
                array(
                    'name' => 'owner',
                    'value' => $this->denormalizeObject(
                        $data,
                        'owner',
                        static::USER_TYPE,
                        $format,
                        array_merge($context, array('mode' => 'short'))
                    )
                ),
                array(
                    'name' => 'assignee',
                    'value' => $this->denormalizeObject(
                        $data,
                        'assignee',
                        static::USER_TYPE,
                        $format,
                        array_merge($context, array('mode' => 'short'))
                    )
                ),
                array(
                    'name' => 'reporter',
                    'value' => $this->denormalizeObject(
                        $data,
                        'reporter',
                        static::USER_TYPE,
                        $format,
                        array_merge($context, array('mode' => 'short'))
                    )
                )
            )
        );
    }

    /**
     * @param Issue $object
     * @param array $valuesData
     */
    protected function setNotEmptyValues(Issue $object, array $valuesData)
    {
        foreach ($valuesData as $data) {
            $value = $data['value'];
            if (!$value) {
                continue;
            }
        }
    }

    /**
     * @param array $data
     * @param string $name
     * @param string $type
     * @param mixed $format
     * @param array $context
     * @return null|object
     */
    protected function denormalizeObject(array $data, $name, $type, $format = null, $context = array())
    {
        $result = null;
        if (!empty($data[$name])) {
            $result = $this->serializer->denormalize($data[$name], $type, $format, $context);
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Issue;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return is_array($data) && $type == static::ISSUE_TYPE;
    }
}
