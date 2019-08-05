<?php

namespace App\Repository;

use App\Entity\PropertySearch;
use Doctrine\ORM\Query;
use App\Entity\Property;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Property|null find($id, $lockMode = null, $lockVersion = null)
 * @method Property|null findOneBy(array $criteria, array $orderBy = null)
 * @method Property[]    findAll()
 * @method Property[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PropertyRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Property::class);
    }

    /**
     * @return Query
     */
    public function findAllVisibleQuery(PropertySearch $search): Query
    {
        $query = $this->findVisibleQuery();
        if($search->getMaxPrice()){
            $query = $query
                ->andWhere('p.price <= :maxprice')
                ->setParameter('maxprice', $search->getMaxPrice());
        }
        if($search->getMinSurface()){
            $query = $query
                ->andWhere('p.surface >= :minsurface')
                ->setParameter('minsurface', $search->getMinSurface());
        }
        if($search->getOptions()->count() > 0){
            $k = 0;
             foreach($search->getOptions() as $option){
                 $k++;
                 $query = $query     
                    ->andWhere(":option$k MEMBER OF p.options")
                    ->setParameter("option$k", $option);
             }
        }
           if($search->getDepartment()){
           $query = $query
                ->andWhere('p.department = :department')
                ->setParameter('department', $search->getDepartment());
        }
           if($search->getCityId()){
           $query = $query
                ->andWhere('p.cityId = :cityId')
                ->setParameter('cityId', $search->getCityId());
        }
            return $query->getQuery();

    }
    /**
     * @return Query
     */
    public function findAllDepatmentQuery(): Query
    {
        $query = $this->findOneByIdJoinedToCategory();       
            return $query->getQuery();
    }

     /**
     * @return Property[]
     */
    public function findLatest(): array
    {
        return $this->findVisibleQuery()
                    ->setMaxResults(4)
                    ->getQuery()
                    ->getResult()
        ; 
    }

    public function findVisibleDepartmentQuery(PropertySearch $search): QueryBuilder
    {
              if($search->getDepartment()){
           $query = $query
                ->andWhere('p.department = :department')
                ->setParameter('department', $search->getDepartment());
        }
        return $query->getQuery();
    }

       private function findVisibleQuery(): QueryBuilder
    {
        return $this->createQueryBuilder('p')
            ->Where('p.sold = false')
        ;
    }
}
