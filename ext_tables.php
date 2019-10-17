<?php
defined('TYPO3_MODE') || die('Access denied.');

call_user_func(
    function()
    {
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
			'ITX.jobs',
			'Frontend',
			[
				'Posting' => 'list'
			],
			// non-cacheable actions
			[
				'Posting' => '',
			]
);

        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
            'ITX.Jobs',
            'Frontend',
            'Jobs'
        );

        if (TYPO3_MODE === 'BE') {

            \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
                'ITX.Jobs',
                'web', // Make module a submodule of 'web'
                'backend', // Submodule key
                '', // Position
                [
                    'Posting' => 'list, show, new, create, edit, update, delete, ','Contact' => 'list, show, new, create, edit, update, delete','Location' => 'list, show, new, create, edit, update, delete','Application' => 'list, show, new, create, edit, update, delete',
                ],
                [
                    'access' => 'user,group',
                    'icon'   => 'EXT:jobs/Resources/Public/Icons/user_mod_backend.svg',
                    'labels' => 'LLL:EXT:jobs/Resources/Private/Language/locallang_backend.xlf',
                ]
            );

        }

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('jobs', 'Configuration/TypoScript', 'Jobs');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jobs_domain_model_posting', 'EXT:jobs/Resources/Private/Language/locallang_csh_tx_jobs_domain_model_posting.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jobs_domain_model_posting');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jobs_domain_model_contact', 'EXT:jobs/Resources/Private/Language/locallang_csh_tx_jobs_domain_model_contact.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jobs_domain_model_contact');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jobs_domain_model_location', 'EXT:jobs/Resources/Private/Language/locallang_csh_tx_jobs_domain_model_location.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jobs_domain_model_location');

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_jobs_domain_model_application', 'EXT:jobs/Resources/Private/Language/locallang_csh_tx_jobs_domain_model_application.xlf');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_jobs_domain_model_application');

    }
);
