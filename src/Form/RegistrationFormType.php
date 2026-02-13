<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\EqualTo;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
			->add('email', null, [
				'label' => 'form.registration.email',
			])
			->add('firstName', null, [
				'label' => 'form.registration.first_name',
			])
			->add('lastName', null, [
				'label' => 'form.registration.last_name',
			])
			->add('agreeTerms', CheckboxType::class, [
				'label' => 'form.registration.agree_terms',
				'mapped' => false,
				'constraints' => [
					new IsTrue(message: 'form.registration.errors.agree_terms'),
				],
			])
			->add('plainPassword', PasswordType::class, [
				'label' => 'form.registration.password',
				'mapped' => false,
				'attr' => ['autocomplete' => 'new-password'],
				'constraints' => [
					new NotBlank(message: 'form.registration.errors.password_blank'),
					new Length(
						min: 6,
						minMessage: 'form.registration.errors.password_min',
						max: 4096,
					),
				],
			])
			->add('confirmPassword', PasswordType::class, [
				'label' => 'form.registration.password_confirm',
				'mapped' => false,
				'attr' => ['autocomplete' => 'new-password'],
				'constraints' => [
					new NotBlank(message: 'form.registration.errors.password_confirm_blank'),
					new EqualTo([
						'propertyPath' => 'plainPassword',
						'message' => 'form.registration.errors.password_confirm_mismatch',
					]),
				],
			])
		;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
