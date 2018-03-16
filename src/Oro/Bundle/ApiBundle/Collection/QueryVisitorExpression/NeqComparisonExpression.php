<?php

namespace Oro\Bundle\ApiBundle\Collection\QueryVisitorExpression;

use Doctrine\Common\Collections\Expr\Comparison;
use Oro\Bundle\ApiBundle\Collection\QueryExpressionVisitor;
use Oro\Component\DoctrineUtils\ORM\QueryBuilderUtil;

/**
 * Represents NOT EQUAL TO comparison expression.
 */
class NeqComparisonExpression implements ComparisonExpressionInterface
{
    /**
     * {@inheritdoc}
     */
    public function walkComparisonExpression(
        QueryExpressionVisitor $visitor,
        Comparison $comparison,
        $fieldName,
        $parameterName
    ) {
        QueryBuilderUtil::checkIdentifier($parameterName);
        QueryBuilderUtil::checkField($fieldName);

        $value = $visitor->walkValue($comparison->getValue());
        if (null === $value) {
            return $visitor->getExpressionBuilder()->isNotNull($fieldName);
        }

        // set parameter
        $visitor->addParameter($parameterName, $value);

        // generate expression
        return $visitor->getExpressionBuilder()
            ->neq($fieldName, $visitor->buildPlaceholder($parameterName));
    }
}
