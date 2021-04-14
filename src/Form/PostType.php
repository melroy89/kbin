<?php declare(strict_types=1);

namespace App\Form;

use App\DTO\PostDto;
use App\Entity\Magazine;
use App\Form\Constraint\ImageConstraint;
use App\Form\EventListener\ImageListener;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    private ImageListener $imageListener;

    public function __construct(ImageListener $imageListener)
    {
        $this->imageListener = $imageListener;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('body', TextareaType::class)
            ->add(
                'image',
                FileType::class,
                [
                    'constraints' => ImageConstraint::default(),
                    'mapped'      => false,
                ]
            )
            ->add('isAdult', CheckboxType::class)
            ->add('submit', SubmitType::class);

        $builder->addEventSubscriber($this->imageListener);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => PostDto::class,
            ]
        );
    }
}
