<?php

	namespace ITX\Jobs\Controller;

	use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
	use TYPO3\CMS\Core\Page\PageRenderer;
	use TYPO3\CMS\Extbase\Mvc\Exception\InvalidArgumentValueException;
	use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
	use TYPO3\CMS\Core\MetaTag\MetaTagManagerRegistry;
	use ITX\Jobs\PageTitle\JobsPageTitleProvider;
	use TYPO3\CMS\Core\Utility\GeneralUtility;

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
	 * PostingController
	 */
	class PostingController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
	{

		/**
		 * postingRepository
		 *
		 * @var \ITX\Jobs\Domain\Repository\PostingRepository
		 * @TYPO3\CMS\Extbase\Annotation\Inject
		 */
		protected $postingRepository = null;

		/**
		 * locationRepository
		 *
		 * @var \ITX\Jobs\Domain\Repository\LocationRepository
		 * @TYPO3\CMS\Extbase\Annotation\Inject
		 */
		protected $locationRepository = null;

		/**
		 * signalSlotDispatcher
		 *
		 * @var \TYPO3\CMS\Extbase\SignalSlot\Dispatcher
		 * @TYPO3\CMS\Extbase\Annotation\Inject
		 */
		protected $signalSlotDispatcher;

		public function initializeAction()
		{

		}

		/**
		 * action list
		 *
		 * @param ITX\Jobs\Domain\Model\Posting
		 *
		 * @return void
		 * @throws \TYPO3\CMS\Extbase\Mvc\Exception\NoSuchArgumentException
		 * @throws InvalidArgumentValueException
		 */
		public function listAction()
		{
			$divisionName = "";
			$careerLevelType = "";
			$selectedEmploymentType = "";
			$selectedLocation = -1;
			$category_str = $this->settings["categories"];
			$categories = array();

			if ($category_str != '')
			{
				$categories = explode(",", $category_str);
			}

			$divisions = $this->postingRepository->findAllDivisions($categories);
			$careerLevels = $this->postingRepository->findAllCareerLevels($categories);
			$employmentTypes = $this->postingRepository->findAllEmploymentTypes($categories);
			$locations = $this->locationRepository->findAll($categories)->toArray();

			if ($this->request->hasArgument("division") &&
				$this->request->hasArgument("careerLevel") &&
				$this->request->hasArgument("employmentType") &&
				$this->request->hasArgument("location"))
			{
				$divisionName = $this->request->getArgument('division');
				$careerLevelType = $this->request->getArgument('careerLevel');
				$selectedEmploymentType = $this->request->getArgument('employmentType');
				$selectedLocation = $this->request->getArgument('location') ? intval($this->request->getArgument('location')) : -1;

				// Prepare for sanity check by aggregating all possible values
				$tmp_divisions = array_column($divisions, "division");
				$tmp_careerLevels = array_column($careerLevels, "careerLevel");
				$tmp_employmentTypes = array_column($employmentTypes, "employmentType");

				$tmp_divisions[] = "";
				$tmp_careerLevels[] = "";
				$tmp_employmentTypes[] = "";

				$locationUids = array_map(function ($element) {
					return $element->getUid();
				}, $locations);
				$locationUids[] = -1;

				// Check for user input sanity
				$result_division = in_array($divisionName, $tmp_divisions);
				$result_careerLevel = in_array($careerLevelType, $tmp_careerLevels);
				$result_employmentType = in_array($selectedEmploymentType, $tmp_employmentTypes);
				$result_location = in_array($selectedLocation, $locationUids);

				if (!$result_division || !$result_careerLevel || !$result_employmentType || !$result_location)
				{
					throw new InvalidArgumentValueException("Input not valid.");
				}
			}

			if ($divisionName != "" || $careerLevelType != "" || $selectedEmploymentType != "" || $selectedLocation != -1)
			{
				$postings = $this->postingRepository->findByFilter($divisionName, $careerLevelType, $selectedEmploymentType, $selectedLocation, $categories);

			}
			else
			{
				if (count($categories) == 0)
				{
					$postings = $this->postingRepository->findAll();
				}
				else
				{
					$postings = $this->postingRepository->findByCategory($categories);
				}
			}

			// SignalSlotDispatcher BeforePostingAssign
			$changedPostings = $this->signalSlotDispatcher->dispatch(__CLASS__, "BeforePostingAssign", ["postings" => $postings]);
			if ($changedPostings["postings"])
			{
				$postings = $changedPostings['postings'];
			}

			$this->view->assign('divisionName', $divisionName);
			$this->view->assign('careerLevelType', $careerLevelType);
			$this->view->assign('selectedEmploymentType', $selectedEmploymentType);
			$this->view->assign('selectedLocation', $selectedLocation);
			$this->view->assign('employmentTypes', $employmentTypes);
			$this->view->assign('postings', $postings);
			$this->view->assign('divisions', $divisions);
			$this->view->assign('careerLevels', $careerLevels);
			$this->view->assign('locations', $locations);
		}

		/**
		 * action show
		 *
		 * @param ITX\Jobs\Domain\Model\Posting
		 *
		 * @return void
		 */
		public function showAction(\ITX\Jobs\Domain\Model\Posting $posting = null)
		{

			$titleProvider = GeneralUtility::makeInstance(JobsPageTitleProvider::class);

			// Meta-Tags setzen. 
			/* TODO: Meta Tags können auch per Viewhelper im Template gesetzt werden. Dazu dann aber vhs als dependency der Extension setzen.
			Hätte den Vorteil, dass es Leute auch entfernen können, falls sie es aus irgendwelchen Gründen nicht haben wollen. 
			Vielleicht in eigenem eigenen Abschnitt oder Partial. Überlasse ich aber letztendlich dir, du kannst es auch so lassen, wenn du es so wirklich besser findest.
			https://t3g.at/opengraph-meta-informationen-typo3/
			https://docs.typo3.org/other/typo3/view-helper-reference/9.5/en-us/typo3/fluid/latest/Format/StripTags.html
			*/
			$metaTagManager = GeneralUtility::makeInstance(MetaTagManagerRegistry::class);
			// @extensionScannerIgnoreLine
			$metaTagManager->getManagerForProperty("description")->addProperty("description", strip_tags($posting->getJobDescription()));
			// @extensionScannerIgnoreLine
			$metaTagManager->getManagerForProperty("og:title")->addProperty("og:title", $posting->getTitle());
			// @extensionScannerIgnoreLine
			$metaTagManager->getManagerForProperty("og:description")->addProperty("og:description", strip_tags($posting->getJobDescription()));
			if ($posting->getListViewImage())
			{
				// @extensionScannerIgnoreLine
				$metaTagManager->getManagerForProperty("og:image")->addProperty("og:image", $this->request->getBaseUri().$posting->getListViewImage()->getOriginalResource()->getPublicUrl());
			}

			$extconf = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(ExtensionConfiguration::class);

			//Google Jobs
			// TODO: evtl über setting abschaltbar machen, falls nicht gewünscht.
			$hiringOranization = array(
				"@type" => "Organization",
				"name" => $extconf->get('jobs', 'companyName')
			);

			if ($hiringOranization['name'] && $this->settings['enableGoogleJobs'])
			{
				$logo = $extconf->get('jobs', 'logo');
				if ($logo)
				{
					$hiringOranization['hiringOranization']["logo"] = $logo;
				}

				switch ($posting->getEmploymentType())
				{
					case "fulltime":
						$employmentType = "FULL_TIME";
						break;
					case "parttime":
						$employmentType = "PART_TIME";
						break;
					case "contractor":
						$employmentType = "CONTRACTOR";
						break;
					case "temporary":
						$employmentType = "TEMPORARY";
						break;
					case "intern":
						$employmentType = "INTERN";
						break;
					case "volunteer":
						$employmentType = "VOLUNTEER";
						break;
					case "perdiem":
						$employmentType = "PER_DIEM";
						break;
					case "other":
						$employmentType = "OTHER";
						break;
					default:
						$employmentType = "";
				}

				$googleJobsJSON = array(
					"@context" => "http://schema.org",
					"@type" => "JobPosting",
					"datePosted" => $posting->getDatePosted()->format("c"),
					"description" => $posting->getCompanyDescription()."<br>".$posting->getJobDescription()."<br>"
						.$posting->getRoleDescription()."<br>".$posting->getSkillRequirements()
						."<br>".$posting->getBenefits(),
					"jobLocation" => [
						"@type" => "Place",
						"address" => [
							"streetAddress" => $posting->getLocation()->getAddressStreetAndNumber(),
							"addressLocality" => $posting->getLocation()->getAddressCity(),
							"postalCode" => strval($posting->getLocation()->getAddressPostCode()),
							"addressCountry" => $posting->getLocation()->getAddressCountry()
						]
					],
					"title" => $posting->getTitle(),
					"employmentType" => $employmentType
				);

				$googleJobsJSON["hiringOrganization"] = $hiringOranization;

				if ($posting->getBaseSalary())
				{
					$currency = $logo = $extconf->get('jobs', 'currency') ?: "EUR";
					$googleJobsJSON["baseSalary"] = [
						"@type" => "MonetaryAmount",
						"currency" => $currency,
						"value" => [
							"@type" => "QuantitativeValue",
							"value" => preg_replace('/\D/', '', $posting->getBaseSalary()),
							"unitText" => "YEAR"
						]
					];
				}
				if ($posting->getValidThrough())
				{
					$googleJobsJSON["validThrough"] = $posting->getValidThrough()->format("c");
				}

				$googleJobs = "<script type=\"application/ld+json\">".strval(json_encode($googleJobsJSON))."</script>";

				$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
				$pageRenderer->addHeaderData($googleJobs);
			}

			// Pagetitle Templating
			$title = $this->settings["pageTitle"];
			if ($title != "")
			{
				$title = str_replace("%postingTitle%", $posting->getTitle(), $title);
			}
			else
			{
				$title = $posting->getTitle();
			}

			$titleProvider->setTitle($title);

			// SignalSlotDispatcher BeforePostingShowAssign
			$changedPosting = $this->signalSlotDispatcher->dispatch(__CLASS__, "BeforePostingShowAssign", ["posting" => $posting]);
			if ($changedPosting["posting"])
			{
				$posting = $changedPosting['posting'];
			}

			$this->view->assign('posting', $posting);
		}
	}
