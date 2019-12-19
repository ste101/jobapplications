<?php

	namespace ITX\Jobs\Domain\Repository;

	include_once 'RepoHelpers.php';
	/***
	 *
	 * This file is part of the "Jobs" Extension for TYPO3 CMS.
	 *
	 * For the full copyright and license information, please read the
	 * LICENSE.txt file that was distributed with this source code.
	 *
	 *  (c) 2019 Stefanie Döll, it.x informationssysteme gmbh
	 *           Benjamin Jasper, it.x informationssysteme gmbh
	 *
	 ***/

	/**
	 * The repository for Locations
	 */
	class LocationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
	{
		/**
		 * Returns all objects of this repository.
		 *
		 * @param $categories array
		 *
		 * @return QueryResultInterface|array
		 */
		public function findAll(array $categories = null)
		{
			$query = $this->createQuery();
			$statementAddition = "";
			if (count($categories) == 0)
			{
				$query->statement(
					"SELECT * FROM tx_jobs_domain_model_location
						  WHERE deleted = 0 AND hidden = 0");
			}
			else
			{
				$statementAddition = buildCategoriesToSQL($categories);

				$statement = "SELECT * FROM tx_jobs_domain_model_location 
						  	  WHERE uid IN 
						  	  (SELECT location FROM tx_jobs_domain_model_posting 
						  	  	JOIN sys_category_record_mm ON tx_jobs_domain_model_posting.uid = sys_category_record_mm.uid_foreign 
						  	  	WHERE deleted = 0 AND hidden = 0 ".$statementAddition.")";

				$query->statement($statement);
			}

			return $query->execute();
		}
	}
