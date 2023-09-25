<?php

namespace App\Validator;

use Sonata\MediaBundle\Model\Media;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class SonataMediaValidator extends ConstraintValidator
{
    /**
     * @var \App\Validator\SonataMedia $constraint
     * @var \Sonata\MediaBundle\Model\Media $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (! $constraint instanceof SonataMedia) {
            throw new UnexpectedTypeException($constraint, SonataMedia::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        if (! $value instanceof Media) {
            throw new UnexpectedValueException($value, Media::class);
        }

        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $binaryContent */
        $binaryContent = $value->getBinaryContent();

        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);
        $validator->validate($binaryContent, $constraint->constrains);
    }
}
