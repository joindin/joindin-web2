<?php
namespace Event\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @Annotation
 */
class ValidEventIconValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        $groupName = $constraint->groupname;
        $keyName   = $constraint->keyname;

        $files = $_FILES;
        if ($groupName) {
            if (!isset($_FILES[$groupName])) {
                return;
            }
            $files = $_FILES[$groupName];
        }

        if (isset($files['error']) && isset($files['error'][$keyName])
            && $files['error'][$keyName] === UPLOAD_ERR_OK) {
            $filename = $files['name'][$keyName];
            // we have a file - is it valid?
            $contents = file_get_contents($files['tmp_name'][$keyName]);
            $image    = @imagecreatefromstring($contents);
            if ($image === false) {
                $this->context->buildViolation("'%filename%' is not a recognised image file")
                    ->setParameter('%filename%', $filename)
                    ->addViolation();
                return;
            }

            $width  = imagesx($image);
            $height = imagesy($image);
            if ($width !== $height) {
                imagedestroy($image);
                $this->context->buildViolation("'%filename%' is not a square image")
                    ->setParameter('%filename%', $filename)
                    ->addViolation();
                return;
            }

            // we got here - image is fine.
            imagedestroy($image);
        }
    }
}
