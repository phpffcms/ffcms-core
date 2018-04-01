<?php

namespace Ffcms\Core\Network\Request;

use Ffcms\Core\App;
use Ffcms\Core\Helper\Type\Any;
use Ffcms\Core\Helper\Type\Arr;
use Ffcms\Core\Helper\Type\Str;
use Ffcms\Templex\Url\UrlRepository;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Trait MultiLanguageFeatures. Multilanguage features for Request controller.
 * @package Ffcms\Core\Network\Request
 * @property ParameterBag $query
 */
trait MultiLanguageFeatures
{
    protected $language;
    protected $languageInPath = false;

    /**
     * Build multi language pathway binding.
     * @return void
     */
    private function runMultiLanguage(): void
    {
        // check if multi-language is enabled
        if (!App::$Properties->get('multiLanguage')) {
            $this->language = App::$Properties->get('singleLanguage');
            return;
        }

        // check if domain-lang binding is enabled
        if (Any::isArray(App::$Properties->get('languageDomainAlias'))) {
            /** @var array $domainAlias */
            $domainAlias = App::$Properties->get('languageDomainAlias');
            if (Any::isArray($domainAlias) && !Str::likeEmpty($domainAlias[$this->getHost()])) {
                $this->language = $domainAlias[$this->getHost()];
            }
            return;
        }

        // try to find language in pathway
        foreach (App::$Properties->get('languages') as $lang) {
            if (Str::startsWith('/' . $lang, $this->getPathInfo())) {
                $this->language = $lang;
                $this->languageInPath = true;
            }
        }

        // try to find in ?lang get
        if (!$this->language && Arr::in($this->query->get('lang'), App::$Properties->get('languages'))) {
            $this->language = $this->query->get('lang');
        }

        // language still not defined?!
        if (!$this->language) {
            $this->setLanguageFromBrowser();
        }
    }

    /**
     * Set language from browser headers
     * @return void
     */
    private function setLanguageFromBrowser(): void
    {
        $userLang = App::$Properties->get('singleLanguage');
        $browserAccept = $this->getLanguages();
        if (Any::isArray($browserAccept) && count($browserAccept) > 0) {
            foreach ($browserAccept as $bLang) {
                if (Arr::in($bLang, App::$Properties->get('languages'))) {
                    $userLang = $bLang;
                    break; // stop calculating, language is founded in priority
                }
            }
        }

        // parse query string
        $queryString = null;
        if (count($this->query->all()) > 0) {
            $queryString = '?' . http_build_query($this->query->all());
        }

        // build response with redirect to language-based path
        $redirectUrl = $this->getSchemeAndHttpHost() . $this->basePath . '/' . $userLang . $this->getPathInfo() . $queryString;
        $response = new RedirectResponse($redirectUrl);
        $response->send();
        exit();
    }

    /**
     * Get current language
     * @return string|null
     */
    public function getLanguage(): ?string
    {
        return $this->language;
    }

    /**
     * Set current language
     * @param string $lang
     * @return bool
     */
    public function setLanguage($lang): bool
    {
        if (Arr::in($lang, App::$Properties->get('languages'))) {
            $this->language = $lang;
            return true;
        }

        return false;
    }

    /**
     * Check if language used in path
     * @return bool
     */
    public function languageInPath(): bool
    {
        return (bool)$this->languageInPath;
    }
}
