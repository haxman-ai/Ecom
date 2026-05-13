<?php

declare(strict_types=1);

namespace App\Form\Admin;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderStatusType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
            'label' => 'Statut',
            'choices' => [
                'En attente de paiement' => 'pending_payment',
                'Payée'                  => 'paid',
                'Expédiée'               => 'shipped',
                'Livrée'                 => 'delivered',
                'Annulée'                => 'canceled',
            ],
            'attr' => ['class' => 'form-select'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
