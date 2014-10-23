<?php

namespace DP\Core\GameBundle\Form;

use DP\Core\GameBundle\Entity\Game;
use DP\Core\GameBundle\Entity\GameRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class GameEntityType extends AbstractType
{
    private $type;


    public function __construct($type)
    {
        $this->type = $type;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['disabled'] = !empty($form->getParent()->getData()->getId());
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'label' => 'game.selectGame',
            'class' => 'DPGameBundle:Game',
            'query_builder' => function(GameRepository $repo) {
                return $repo->getAvailableGamesByTypeQB($this->type);
            }
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'dedipanel_game_entity';
    }
}
