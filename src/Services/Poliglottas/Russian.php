<?php

namespace MenaraSolutions\Geographer\Services\Poliglottas;

use MenaraSolutions\Geographer\Contracts\IdentifiableInterface;
use MenaraSolutions\Geographer\Contracts\PoliglottaInterface;
use MenaraSolutions\Geographer\Exceptions\MisconfigurationException;

/**
 * Class Russian
 * @package MenaraSolutions\FluentGeonames\Services\Poliglottas
 */
class Russian extends Base implements PoliglottaInterface
{
    /**
     * @var string
     */
    protected $code = 'ru';
    
    /**
     * @param IdentifiableInterface $subject
     * @param string $form
     * @return string
     * @throws MisconfigurationException
     */
    public function translate(IdentifiableInterface $subject, $form = 'default')
    {
        if (! empty($form) && ! method_exists($this, 'inflict' . ucfirst($form))) {
            throw new MisconfigurationException('Language ' . $this->code . ' doesn\'t inflict to ' . $form);
        }

        $meta = $this->fromCache($subject);
        if (! $meta) return false;
        
        $result = $this->extract($meta, $subject->expectsLongNames(), $form);

        if (! $result) {
            $template = $this->inflictDefault($meta, $subject->expectsLongNames());
            $result = $this->{'inflict' . ucfirst($form)}($template);
        }

        return $result;
    }

    /**
     * @param string $string
     * @param string $form
     * @return null|string
     */
    public function preposition($string, $form)
    {
        switch ($form) {
            case 'in':
                return 'в';

            case 'from':
                return 'из';

            default:
                return null;
        }
    }

    /**
     * @param array $meta
     * @param $long
     * @return string
     */
    protected function inflictDefault(array $meta, $long)
    {
        return $this->extract($meta, $long, 'default');
    }

    /**
     * @param string $template
     * @return string
     */
    protected function inflictIn($template)
    {
        switch ($this->getLastLetter($template)) {
            case 'й':
                $template = $this->removeLastLetter($template) . 'е';

                break;

            case 'л':
                $template .= 'е';

                break;

            case 'т':
            case 'к':
            case 'г':
            case 'м':
            case 'з':
            case 'ш':
            case 'р':
            case 'с':
            case 'д':
            case 'н':
                $template .= 'е';

                break;

            case 'я':
                $template = $this->removeLastLetter($template) . 'и';

                break;

            case 'а':
                $template = $this->removeLastLetter($template) . 'е';

                break;

            default:
        }

        return $template;
    }

    /**
     * @param string $template
     * @return string
     */
    protected function inflictFrom($template)
    {
        switch ($this->getLastLetter($template)) {
            case 'й':
                $template = $this->removeLastLetter($template) . 'я';

                break;

            case 'л':
                $template .= 'а';

                break;

            case 'т':
            case 'к':
            case 'г':
            case 'м':
            case 'з':
            case 'ш':
            case 'р':
            case 'с':
            case 'д':
            case 'н':
                $template .= 'а';

                break;

            case 'я':
                $template = $this->removeLastLetter($template) . 'и';

                break;

            case 'а':
                $template = $this->removeLastLetter($template) . 'ы';

                break;

            default:
        }

        return $template;
    }

    /**
     * @param $string
     * @return string
     */
    private function getLastLetter($string)
    {
        return mb_substr($string, mb_strlen($string) - 1);
    }

    /**
     * @param $string
     * @return string
     */
    private function removeLastLetter($string)
    {
        return mb_substr($string, 0, mb_strlen($string) - 1);
    }

    /**
     * @param array $meta
     * @param bool $long
     * @param string $form
     * @return string
     */
    private function extract(array $meta, $long, $form)
    {
        $fields = ['long', 'short'];

        if (! $long) $fields = array_reverse($fields);

        return isset($meta[$fields[0]][$form]) ? $meta[$fields[0]][$form] :
            $meta[$fields[1]][$form];
    }
}