<?php

namespace AminulBD\DotBD;

use GuzzleHttp\Client as Http;
use GuzzleHttp\Cookie\FileCookieJar;
use Symfony\Component\DomCrawler\Crawler;

class Finder
{
	protected $http;

	protected $token;

	protected $name;

	protected $ext;

	private $crawler;

	function __construct()
	{
		$file = dirname(__FILE__) . '/../cookies.json';
		$jar = new FileCookieJar($file, true);

		$this->http = new Http([
			'base_uri' => 'https://bdia.btcl.com.bd/',
			'cookies' => $jar,
			'http_errors' => false,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
				'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.116 Safari/537.36',
				'Referer' => 'https://bdia.btcl.com.bd/',
			]
		]);

		$this->token = $this->token();
	}

	private function token() {

		$request = $this->http->get('/');

		if ($request->getStatusCode() !== 200) {
			return null;
		}

		$body = $request->getBody()->getContents();
		$crawler = new Crawler($body);
		$tokenEl = $crawler->filter('#submit_for_check > input[name="csrfPreventionSalt"]')->first();
		$token = $tokenEl->count() > 0 ? $tokenEl->attr('value') : null;

		return $token;
	}

	public function name($name) {
		$this->name = trim($name);

		return $this;
	}

	public function ext($ext) {
		// TODO: Validate
		// $exts = [
		// 	'.com.bd', '.edu.bd', '.gov.bd', '.net.bd', '.org.bd', '.ac.bd', '.mil.bd', '.co.bd', '.info.bd', '.বাংলা', '.tv.bd', '.sw.bd'
		// ];
		$this->ext = trim($ext);

		return $this;
	}

	public function domain() {
		return $this->name . $this->ext;
	}

	public function check() {
		$request = $this->http->post('/DomainChecker.do', [
			'query' => [
				'mode' => 'checkDomain'
			],
			'body' => http_build_query([
				'csrfPreventionSalt' => $this->token,
				'searchType' => 'ajax',
				'domainName' => $this->name,
				'domainExt' => $this->ext,
			]),
		]);

		if ($request->getStatusCode() == 200) {
			$body = str_replace('	>', '', $request->getBody()->getContents());
			$this->crawler = new Crawler($body);
		}

		return $this;
	}

	public function eligible() {
		$nonAbilityEl = $this->crawler->filter('.domain-available')->first();

		return $nonAbilityEl->count() > 0;
	}

	public function whois() {
		$person = [
			'name' => null,
			'email' => null,
			'status' => null,
			'register_date' => null,
			'expiry_date' => null,
		];
		if ($this->eligible() || $this->crawler->filter('.row > .col-md-6:first-child table')->count() <= 1) {
			return $person;
		}

		$this->crawler->filter('.row > .col-md-6:first-child table > tbody > tr > td')->each(function(Crawler $crawler, $i) use (&$person) {
			switch ($i) {
				case 1:
					$person['name'] = $crawler->text();
					break;

				case 2:
					$person['email'] = $crawler->text();
					break;

				case 3:
					$person['status'] = $crawler->text();
					break;

				case 4:
					$person['register_date'] = $crawler->text();
					break;

				case 5:
					$person['expiry_date'] = $crawler->text();
					break;
			}
		});

		return array_map('trim', $person);
	}

	public function available() {
		$domains = [];
		$this->crawler->filter('table.price-table > tbody tr td:first-child')->each(function (Crawler $crawler) use (&$domains) {
			$domains[] = $crawler->text();
		});

		return array_map('trim', $domains);
	}
}
