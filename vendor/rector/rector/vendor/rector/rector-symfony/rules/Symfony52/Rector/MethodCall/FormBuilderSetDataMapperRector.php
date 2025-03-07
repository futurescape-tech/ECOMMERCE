<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony52\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
 * @see \Rector\Symfony\Tests\Symfony52\Rector\MethodCall\FormBuilderSetDataMapperRector\FormBuilderSetDataMapperRectorTest
 */
final class FormBuilderSetDataMapperRector extends AbstractRector
{
    /**
     * @var string
     */
    private const DATAMAPPER_INTERFACE = 'Symfony\\Component\\Form\\DataMapperInterface';
    /**
     * @var string
     */
    private const DATAMAPPER_CLASS = 'Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\DataMapper';
    /**
     * @readonly
     */
    private ObjectType $objectType;
    /**
     * @readonly
     */
    private ObjectType $dataMapperObjectType;
    public function __construct()
    {
        $this->objectType = new ObjectType(self::DATAMAPPER_INTERFACE);
        $this->dataMapperObjectType = new ObjectType(self::DATAMAPPER_CLASS);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrates from deprecated Form Builder->setDataMapper(new PropertyPathMapper()) to Builder->setDataMapper(new DataMapper(new PropertyPathAccessor()))', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;

class SomeClass
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->setDataMapper(new PropertyPathMapper());
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;
use Symfony\Component\Form\FormConfigBuilderInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\DataMapper;
use Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor;

class SomeClass
{
    public function run(FormConfigBuilderInterface $builder)
    {
        $builder->setDataMapper(new DataMapper(new PropertyPathAccessor()));
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node->name, 'setDataMapper')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Form\\FormConfigBuilderInterface'))) {
            return null;
        }
        $args = $node->getArgs();
        $firstArg = $args[0];
        $argumentValue = $firstArg->value;
        if ($this->isObjectType($argumentValue, $this->objectType) || $this->isObjectType($argumentValue, $this->dataMapperObjectType)) {
            return null;
        }
        $new = new New_(new FullyQualified('Symfony\\Component\\Form\\Extension\\Core\\DataAccessor\\PropertyPathAccessor'));
        $firstArg->value = new New_(new FullyQualified(self::DATAMAPPER_CLASS), [new Arg($new)]);
        return $node;
    }
}
