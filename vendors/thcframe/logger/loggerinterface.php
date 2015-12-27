<?php

namespace THCFrame\Logger;

/**
 * Popisuje instanci loggeru
 *
 * Zpráva MUSÍ být string nebo objekt, který implementuje __toString().
 *
 * Zpráva MŮŽE obsahovat zástupné identifikátory ve formě {foo}, kde foo
 * bude nahrazeno kontextovými daty pod klíčem "foo".
 *
 * Kontextové pole může obsahovat libovolná data, jediným předpokladem
 * implementátora je, že pokud je předávána instance Exception za účelem
 * produkování stack trace, MUSÍ být v klíči jménem "exception".
 *
 */
interface LoggerInterface
{

    /**
     * Systém je nepoužitelný.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function emergency($message, array $context = array());

    /**
     * Je nutné ihned provést akci.
     *
     * Příklad: Celá stránka je mimo provoz, databáze nedostupná a podobně. Metoda by
     * měla spustit SMS upozornění a vzbudit vás.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function alert($message, array $context = array());

    /**
     * Kritické podmínky.
     *
     * Příklad: Komponenta aplikace je nedostupná, neočekávaná výjimka.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function critical($message, array $context = array());

    /**
     * Běhové chyby, které nevyžadují okamžitou akci, ale měly by být typicky
     * logovány a sledovány.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function error($message, array $context = array());

    /**
     * Výjimečné události, které nejsou chybami.
     *
     * Příklad: Použití zastaralého API, nesprávné použití API, nevhodné věci,
     * které nemusí být nutně špatně.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function warning($message, array $context = array());

    /**
     * Normální, ale podstatné události.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function notice($message, array $context = array());

    /**
     * Zajímavé události.
     *
     * Příklad: Uživatelská přihlášení, SQL logy.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function info($message, array $context = array());

    /**
     * Detailní ladící informace.
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function debug($message, array $context = array());

    /**
     * Logování sql dotazů
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function sql($message, array $context = array());
    
    /**
     * Logování událostí cronu
     *
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function cron($message, array $context = array());
    
    /**
     * Zaloguje s libovolnou úrovní.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return \THCFrame\Logger\LoggerInterface
     */
    public function log($level, $message, array $context = array());
}
