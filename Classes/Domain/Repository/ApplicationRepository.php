<?php

	namespace ITX\Jobs\Domain\Repository;

	use ITX\Jobs\Domain\Model\Contact;
	use ITX\Jobs\Domain\Model\Posting;
	use TYPO3\CMS\Core\Database\ConnectionPool;
	use TYPO3\CMS\Core\Utility\GeneralUtility;
	use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

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
	 * The repository for Applications
	 */
	class ApplicationRepository extends \TYPO3\CMS\Extbase\Persistence\Repository
	{
		/**
		 * Function for filtering applications
		 *
		 * @param $contact int
		 * @param $posting int
		 * @param $status  int
		 */
		public function findByFilter(int $contact, int $posting, int $status, int $archived = 0, string $orderBy = "crdate", string $order = "ASC")
		{
			$contactSQL = "";
			$postingSQL = "";
			$statusSQL = "";

			$baseSQL = "SELECT * FROM tx_jobs_domain_model_application WHERE archived = ".$archived." ";

			if ($contact)
			{
				$contactSQL = "AND posting IN( SELECT uid FROM tx_jobs_domain_model_posting 
								WHERE contact = ".$contact.")";
			}
			if ($posting)
			{
				$postingSQL = "AND posting = \"$posting\"";
			}
			if ($status)
			{
				$postingSQL = "AND status = $status";
			}

			$query = $this->createQuery();

			$query->statement(
				$baseSQL." ".$contactSQL." ".$postingSQL." ".$statusSQL." ORDER BY ".$orderBy." ".$order
			);

			return $query->execute();
		}

		/**
		 * Returns all objects of this repository.
		 *
		 * @return QueryResultInterface|array
		 */
		public function findAll()
		{
			$query = $this->createQuery();

			$query->statement("SELECT * FROM tx_jobs_domain_model_application 
										WHERE hidden = 0 AND archived = 0 ORDER BY crdate DESC");

			return $query->execute();
		}

		/**
		 * @param int $contact
		 *
		 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
		 */
		public function findNewApplicationsByContact(int $contact)
		{
			$query = $this->createQuery();

			$query->statement("SELECT * FROM tx_jobs_domain_model_application 
										WHERE hidden = 0 AND archived = 0 AND status = 1 AND posting 
										IN( SELECT uid FROM tx_jobs_domain_model_posting 
										WHERE contact = ".$contact.")");

			return $query->execute();
		}

		/**
		 * Finds applications which are older than or equal the given timestamp
		 *
		 * @param $timestamp int
		 * @param $status bool
		 *
		 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
		 */
		public function findOlderThan(int $timestamp, bool $status = false)
		{
			$query = $this->createQuery();

			if ($status)
			{
				$query->statement("SELECT * FROM tx_jobs_domain_model_application WHERE crdate <= $timestamp
											AND status IN ( SELECT uid FROM tx_jobs_domain_model_status
    										WHERE is_end_status = 1)");
			}
			else
			{
				$query->statement("SELECT * FROM tx_jobs_domain_model_application WHERE crdate <= $timestamp");
			}

			return $query->execute();
		}

		/**
		 * Finds applications which are older than or equal the given timestamp and which are not anonymized
		 *
		 * @param $timestamp int
		 * @param $status bool
		 *
		 * @return array|\TYPO3\CMS\Extbase\Persistence\QueryResultInterface
		 */
		public function findNotAnonymizedOlderThan(int $timestamp, bool $status = false)
		{
			$query = $this->createQuery();

			if ($status)
			{
				$query->statement("SELECT * FROM tx_jobs_domain_model_application WHERE crdate <= $timestamp
											AND last_name != '***'
											AND status IN ( SELECT uid FROM tx_jobs_domain_model_status
    										WHERE is_end_status = 1)");
			}
			else
			{
				$query->statement("SELECT * FROM tx_jobs_domain_model_application WHERE crdate <= $timestamp 
											AND last_name != '***'");
			}

			return $query->execute();
		}
	}
