<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

/**
 * Code coverage checker. Analyzes a given `clover.xml` report produced
 * by PHPUnit and checks if coverage fits expected ratio
 *
 * Usage:
 *     php coverage-checker <pass-percentage> <paths-to-clover>
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */

$percentage = min(100, max(0, (int) $argv[1]));

if (!$percentage) {
    throw new InvalidArgumentException('An integer checked percentage must be given as second parameter');
}

$inputFile = $argc > 2 ? $argv[2] : "./test/clover.xml";

if (!file_exists($inputFile)) {
    throw new InvalidArgumentException("$inputFile: Invalid input file provided");
}

$xml = new SimpleXMLElement(file_get_contents($inputFile));
/* @var $metrics SimpleXMLElement[] */
$metrics = $xml->xpath('//metrics');

$totalElements = 0;
$checkedElements = 0;

foreach ($metrics as $metric) {
    $totalElements   += (int) $metric['elements'];
    $checkedElements += (int) $metric['coveredelements'];
}

$coverage = round(($checkedElements / $totalElements) * 100);

if ($coverage < $percentage) {
    echo "\033[00;31mCode coverage is $coverage%, which is below the accepted $percentage%\033[00m" . PHP_EOL;
    exit(1);
}

echo "\033[00;32mCode coverage is $coverage% - OK!\033[00m" . PHP_EOL;
exit(0);
