<?php

/*
 * This file is part of the xAPI package.
 *
 * (c) Christian Flothmann <christian.flothmann@xabbuh.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xabbuh\XApi\Serializer\Symfony\Normalizer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Xabbuh\XApi\Model\Context;
use Xabbuh\XApi\Model\LanguageMap;
use Xabbuh\XApi\Model\Uuid;

/**
 * Normalizes and denormalizes xAPI statement contexts.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
final class ContextNormalizer extends Normalizer
{
    public function normalize($object, $format = null, array $context = array())
    {
        if (!$object instanceof Context) {
            return;
        }

        $data = array();

        if (null !== $registration = $object->getRegistration()) {
            $data['registration'] = $registration;
        }

        if (null !== $instructor = $object->getInstructor()) {
            $data['instructor'] = $this->normalizeAttribute($instructor, $format, $context);
        }

        if (null !== $team = $object->getTeam()) {
            $data['team'] = $this->normalizeAttribute($team, $format, $context);
        }

        if (null !== $contextActivities = $object->getContextActivities()) {
            $data['contextActivities'] = $this->normalizeAttribute($contextActivities, $format, $context);
        }

        if (null !== $revision = $object->getRevision()) {
            $data['revision'] = $revision;
        }

        if (null !== $platform = $object->getPlatform()) {
            $data['platform'] = $platform;
        }

        if (null !== $language = $object->getLanguage()) {
            $data['language'] = $language;
        }

        if (null !== $statement = $object->getStatement()) {
            $data['statement'] = $this->normalizeAttribute($statement, $format, $context);
        }

        if (null !== $extensions = $object->getExtensions()) {
            $data['extensions'] = $this->normalizeAttribute($extensions, $format, $context);
        }

        if (empty($data)) {
            return new \stdClass();
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Context;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $statementContext = new Context();

        if (array_key_exists('registration', $data)) {
            if (empty($data['registration'])) {
                throw new InvalidArgumentException('Missing registration in context.');
            }

            if (!is_string($data['registration']) || !Uuid::isValid($data['registration'])) {
                throw new UnexpectedValueException('UUID for context registration is no valid.');
            }

            $statementContext = $statementContext->withRegistration($data['registration']);
        }

        if (isset($data['instructor'])) {
            $statementContext = $statementContext->withInstructor($this->denormalizeData($data['instructor'], 'Xabbuh\XApi\Model\Actor', $format, $context));
        }

        if (isset($data['team'])) {
            if (isset($data['team']['objectType']) && 'Group' !== $data['team']['objectType']) {
                throw new \UnexpectedValueException('The "team" property is not a Group.');
            }

            $statementContext = $statementContext->withTeam($this->denormalizeData($data['team'], 'Xabbuh\XApi\Model\Group', $format, $context));
        }

        if (isset($data['contextActivities'])) {
            $statementContext = $statementContext->withContextActivities($this->denormalizeData($data['contextActivities'], 'Xabbuh\XApi\Model\ContextActivities', $format, $context));
        }

        if (isset($data['revision'])) {
            if (!is_string($data['revision'])) {
                throw new \InvalidArgumentException('The "revision" property is not a string.');
            }

            $statementContext = $statementContext->withRevision($data['revision']);
        }

        if (isset($data['platform'])) {
            if (!is_string($data['platform'])) {
                throw new \InvalidArgumentException('The "platform" property is not a string.');
            }

            $statementContext = $statementContext->withPlatform($data['platform']);
        }

        if (isset($data['language'])) {
            if (!LanguageMap::isValidTag($data['language'])) {
                throw new UnexpectedValueException(sprintf('Language code "%s" is not valid.', $data['language']));
            }

            $statementContext = $statementContext->withLanguage($data['language']);
        }

        if (isset($data['statement'])) {
            $statementContext = $statementContext->withStatement($this->denormalizeData($data['statement'], 'Xabbuh\XApi\Model\StatementReference', $format, $context));
        }

        if (isset($data['extensions'])) {
            $statementContext = $statementContext->withExtensions($this->denormalizeData($data['extensions'], 'Xabbuh\XApi\Model\Extensions', $format, $context));
        }

        return $statementContext;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'Xabbuh\XApi\Model\Context' === $type;
    }
}
