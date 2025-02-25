<?php
  /**
   *
   * @copyright 2008 - https://www.clicshopping.org
   * @Brand : ClicShopping(Tm) at Inpi all right Reserved
   * @Licence GPL 2 & MIT
   * @Info : https://www.clicshopping.org/forum/trademark/
   *
   */

  namespace ClicShopping\Apps\Customers\Reviews\Classes\Shop;

  use ClicShopping\OM\Registry;
  use ClicShopping\OM\HTML;
  use ClicShopping\OM\CLICSHOPPING;

  class ReviewsClass
  {
    protected mixed $productsCommon;
    protected mixed $db;
    protected mixed $lang;
    protected mixed $customer;
    protected int $reviews_number_comments;
    protected int $reviews_number_word;

    public function __construct()
    {
      $this->productsCommon = Registry::get('ProductsCommon');
      $this->db = Registry::get('Db');
      $this->lang = Registry::get('Language');
      $this->customer = Registry::get('Customer');

      if (\defined('MODULE_PRODUCTS_INFO_REVIEWS_NUMBER_WORDS')) {
        $this->reviews_number_word = (int)MODULE_PRODUCTS_INFO_REVIEWS_NUMBER_WORDS;
      } else {
        $this->reviews_number_word = 0;
      }

      if (\defined('MODULE_PRODUCTS_INFO_REVIEWS_NUMBER_COMMENTS')) {
        $this->reviews_number_comments = (int)MODULE_PRODUCTS_INFO_REVIEWS_NUMBER_COMMENTS;
      } else {
        $this->reviews_number_comments = 0;
      }
    }

    /**
     * get the total product review
     *
     * @param int $id , $id of the product
     * @return bool the numbeer of the review
     *
     */
    public function getTotalReviews(): int
    {
      if ($this->customer->getCustomersGroupID() == 0 || $this->customer->getCustomersGroupID() == 99) {
        $Qcheck = $this->db->prepare('select count(r.reviews_id) as total
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and rd.languages_id = :languages_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id = 0
                                      ');
        $Qcheck->bindInt(':products_id', $this->productsCommon->getId());
        $Qcheck->bindInt(':languages_id', $this->lang->getId());
        $Qcheck->execute();
      } else {
        $Qcheck = $this->db->prepare('select count(r.reviews_id) as total
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and rd.languages_id = :languages_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id > 0
                                      ');
        $Qcheck->bindInt(':products_id', $this->productsCommon->getId());
        $Qcheck->bindInt(':languages_id', $this->lang->getId());
        $Qcheck->execute();
      }

      return $Qcheck->valueInt('total');
    }

    /**
     * get all review about a product id
     * @return mixed
     */
    public function getData()
    {
      if ($this->customer->getCustomersGroupID() == 0) {
        $Qreviews = $this->db->prepare('select r.reviews_id,
                                                 left(rd.reviews_text, :limitText) as reviews_text,
                                                 r.reviews_rating,
                                                 r.date_added,
                                                 r.status,
                                                 r.customers_name,
                                                 r.customers_id
                                          from :table_reviews r,
                                               :table_reviews_description rd
                                          where r.products_id = :products_id
                                          and r.reviews_id = rd.reviews_id
                                          and rd.languages_id = :languages_id
                                          and r.status = 1
                                          and r.customers_group_id = 0
                                          order by r.reviews_rating desc,
                                                  r.date_added desc
                                          limit :limit
                                          ');
        $Qreviews->bindInt(':products_id', $this->productsCommon->getId());
        $Qreviews->bindInt(':languages_id', $this->lang->getId());
        $Qreviews->bindInt(':limitText', $this->reviews_number_word);
        $Qreviews->bindInt(':limit', $this->reviews_number_comments);
        $Qreviews->execute();
      } else {
        $Qreviews = $this->db->prepare('select r.reviews_id,
                                                 left(rd.reviews_text, :limitText ) as reviews_text,
                                                 r.reviews_rating,
                                                 r.date_added,
                                                 r.status,
                                                 r.customers_name,
                                                 r.customers_id
                                         from :table_reviews r,
                                              :table_reviews_description rd
                                         where r.products_id = :products_id
                                         and r.reviews_id = rd.reviews_id
                                         and rd.languages_id = :languages_id
                                         and r.status = 1
                                         and r.customers_group_id > 0
                                         order by r.reviews_rating desc,
                                                  r.date_added desc
                                         limit :limit
                                        ');
        $Qreviews->bindInt(':products_id', $this->productsCommon->getId());
        $Qreviews->bindInt(':languages_id', $this->lang->getId());
        $Qreviews->bindInt(':limitText', $this->reviews_number_word);
        $Qreviews->bindInt(':limit', $this->reviews_number_comments);
        $Qreviews->execute();
      }

      $this->getPageSetTotalRows = $Qreviews->getPageSetTotalRows();

      return $Qreviews;
    }

    /**
     * Count the total rows
     *
     * @return int : total row
     *
     */
    public function getPageSetTotalRows()
    {
      return $this->getPageSetTotalRows;
    }

    /**
     * Customers has purchased with comment
     *
     * @return array : $Qhaspurchased : purchased informations
     *
     */
    public function hasPurchasedProduct()
    {
      $CLICSHOPPING_Db = Registry::get('Db');
      $CLICSHOPPING_Customer = Registry::get('Customer');
      $CLICSHOPPING_ProductsCommon = Registry::get('ProductsCommon');

      if ($CLICSHOPPING_Customer->getCustomersGroupID() == 0) {
        $Qhaspurchased = $CLICSHOPPING_Db->prepare('select count(*) as total
                                                    from :table_orders o,
                                                         :table_orders_products op,
                                                         :table_products p
                                                    where o.customers_id = :customers_id
                                                    and o.orders_id = op.orders_id
                                                    and op.products_id = p.products_id
                                                    and op.products_id = :products_id
                                                    and o.customers_group_id = 0
                                                    ');
        $Qhaspurchased->bindInt(':customers_id', $CLICSHOPPING_Customer->getID());
        $Qhaspurchased->bindInt(':products_id', $CLICSHOPPING_ProductsCommon->getID());
        $Qhaspurchased->execute();

      } else {
        $Qhaspurchased = $CLICSHOPPING_Db->prepare('select count(*) as total
                                                    from :table_orders o,
                                                         :table_orders_products op,
                                                         :table_products p
                                                    where o.customers_id = :customers_id
                                                    and o.orders_id = op.orders_id
                                                    and op.products_id = p.products_id
                                                    and op.products_id = :products_id
                                                    and o.customers_group_id > 0
                                                    ');
        $Qhaspurchased->bindInt(':customers_id', $CLICSHOPPING_Customer->getID());
        $Qhaspurchased->bindInt(':products_id', $CLICSHOPPING_ProductsCommon->getID());
        $Qhaspurchased->execute();
      }

      return ($Qhaspurchased->fetch() !== false);
    }

    /**
     * Get rewiews with specific reviews id
     * @param int|null $id
     * @return false
     */
    public function getDataReviews(?int $id = null)
    {
      $reviews_id = HTML::sanitize($id);

      if (!\is_null($reviews_id) &&  is_numeric($reviews_id)) {
        if ($this->customer->getCustomersGroupID() == 0 || $this->customer->getCustomersGroupID() == 99) {
          $Qreviews = $this->db->prepare('select r.reviews_id,
                                                rd.reviews_text,
                                                r.reviews_rating,
                                                r.date_added,
                                                r.customers_name,
                                                r.customers_id
                                          from :table_reviews r,
                                               :table_reviews_description rd
                                          where r.reviews_id = :reviews_id
                                          and r.reviews_id = rd.reviews_id
                                          and rd.languages_id = :languages_id
                                          and r.status = 1
                                          and r.customers_group_id = 0
                                          ');
          $Qreviews->bindInt(':reviews_id', $reviews_id);
          $Qreviews->bindInt(':languages_id', $this->lang->getId());
          $Qreviews->execute();
        } else {
          $Qreviews = $this->db->prepare('select r.reviews_id,
                                                  rd.reviews_text,
                                                  r.reviews_rating,
                                                  r.date_added,
                                                  r.customers_name,
                                                  r.customers_id
                                          from :table_reviews r,
                                               :table_reviews_description rd
                                          where r.reviews_id = :reviews_id
                                          and r.reviews_id = rd.reviews_id
                                          and rd.languages_id = :languages_id
                                          and r.status = 1
                                          and r.customers_group_id > 0
                                          ');
          $Qreviews->bindInt(':reviews_id', $reviews_id);
          $Qreviews->bindInt(':languages_id', $this->lang->getId());
          $Qreviews->execute();
        }

        return $Qreviews->fetch();
      } else {
        return false;
      }
    }

    /**
     * Save the review
     *
     * @param string
     * @return string
     *
     */
    public function saveEntry()
    {

      if ($this->customer->getCustomersGroupID() == 0) {
        $array_sql = [
          'products_id' => (int)$this->productsCommon->getID(),
          'customers_id' => (int)$this->customer->getID(),
          'customers_name' => $this->customer->getName(),
          'reviews_rating' => (int)$_POST['rating'],
          'date_added' => 'now()',
          'last_modified' => 'now()',
          'status' => 0,
          'customers_group_id' => 0
        ];

        $this->db->save('reviews', $array_sql);
      } else {
        $array_sql = [
          'products_id' => (int)$this->productsCommon->getID(),
          'customers_id' => (int)$this->customer->getID(),
          'customers_name' => $this->customer->getName(),
          'reviews_rating' => (int)$_POST['rating'],
          'date_added' => 'now()',
          'last_modified' => 'now()',
          'status' => 0,
          'customers_group_id' => (int)$this->customer->getCustomersGroupID()
        ];

        $this->db->save('reviews', $array_sql);
      }

      $insert_id = $this->db->lastInsertId();

      $array_sql =  [
        'reviews_id' => (int)$insert_id,
        'languages_id' => (int)$this->lang->getId(),
        'reviews_text' => HTML::sanitize($_POST['review'])
      ];

      $this->db->save('reviews_description', $array_sql);
    }

    /**
     * Send an email
     *
     * @param string
     * @return string
     *
     */
    public function sendEmail()
    {
      $CLICSHOPPING_Mail = Registry::get('Mail');

      if (REVIEW_COMMENT_SEND_EMAIL == 'true') {
        $email_text = CLICSHOPPING::getDef('email_text_customer', ['store_name' => STORE_NAME]);

        $to_addr = $this->customer->getEmailAddress();
        $from_name = STORE_NAME;
        $from_addr = STORE_OWNER_EMAIL_ADDRESS;
        $to_name =  $this->customer->getLastName();
        $subject = CLICSHOPPING::getDef('email_subject_customer', ['store_name' => STORE_NAME]);

        $CLICSHOPPING_Mail->addHtml($email_text);
        $CLICSHOPPING_Mail->send($to_addr, $from_name, $from_addr, $to_name, $subject);

 //admin
        $email_text = CLICSHOPPING::getDef('email_text', ['store_name' => STORE_NAME]);

        $to_addr = STORE_OWNER_EMAIL_ADDRESS;
        $from_name = STORE_NAME;
        $from_addr = STORE_OWNER_EMAIL_ADDRESS;
        $to_name =  STORE_NAME;
        $subject = CLICSHOPPING::getDef('email_subject', ['store_name' => STORE_NAME]);

        $CLICSHOPPING_Mail->addHtml($email_text .'<br />' . $this->productsCommon->getProductsName());
        $CLICSHOPPING_Mail->send($to_addr, $from_name, $from_addr, $to_name, $subject);
      }
    }


    public function deleteReviews(int $review_id)
    {
      $Odelete = $this->db->prepare('delete
                                    from :table_reviews
                                    where reviews_id = :reviews_id
                                    ');
      $Odelete->bindInt(':reviews_id', $review_id);
      $Odelete->execute();

      $Odelete = $this->db->prepare('delete
                                     from :table_reviews_description
                                     where reviews_id = :reviews_id
                                    ');
      $Odelete->bindInt(':reviews_id', $review_id);
      $Odelete->execute();
    }

    /**
     * @param int $products_id
     * @param bool $all_language
     * @return float
     */
    public function getAverageProductReviews(int $products_id, bool $all_language = false): ?float
    {
      if ($all_language === false) {
        if ($this->customer->getCustomersGroupID() == 0 || $this->customer->getCustomersGroupID() == 99) {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                                sum(r.reviews_rating) as sum_reviews
                                        from :table_reviews r,
                                             :table_reviews_description rd
                                        where r.products_id = :products_id
                                        and r.reviews_id = rd.reviews_id
                                        and r.status = 1
                                        and r.customers_group_id = 0
                                        ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->execute();

        } else {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                               sum(r.reviews_rating) as sum_reviews
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id > 0
                                      ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->execute();
        }

      } else {
        if ($this->customer->getCustomersGroupID() == 0 || $this->customer->getCustomersGroupID() == 99) {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                               sum(r.reviews_rating) as sum_reviews
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and rd.languages_id = :languages_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id = 0
                                      ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->bindInt(':languages_id', $this->lang->getId());
          $Qcheck->execute();
        } else {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                               sum(r.reviews_rating) as sum_reviews
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and rd.languages_id = :languages_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id > 0
                                      ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->bindInt(':languages_id', $this->lang->getId());
          $Qcheck->execute();
        }
      }

      if ($Qcheck->valueInt('reviews_total') > 0) {
        $average = $Qcheck->valueInt('sum_reviews') / $Qcheck->valueInt('reviews_total');
      } else {
        $average = 0;
      }

      return $average;
    }


    /**
     * @param int $products_id
     * @param bool $all_language
     * @return float
     */
    public function getBestProductReviews(int $products_id, bool $all_language = false) :?int
    {
      if ($all_language === false) {
        if ($this->customer->getCustomersGroupID() == 0 || $this->customer->getCustomersGroupID() == 99) {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                                max(r.reviews_rating) as max_reviews
                                        from :table_reviews r,
                                             :table_reviews_description rd
                                        where r.products_id = :products_id
                                        and r.reviews_id = rd.reviews_id
                                        and r.status = 1
                                        and r.customers_group_id = 0
                                        ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->execute();

        } else {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                               max(r.reviews_rating) as max_reviews
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id > 0
                                      ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->execute();
        }
      } else {
        if ($this->customer->getCustomersGroupID() == 0 || $this->customer->getCustomersGroupID() == 99) {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                               max(r.reviews_rating) as max_reviews
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and rd.languages_id = :languages_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id = 0
                                      ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->bindInt(':languages_id', $this->lang->getId());
          $Qcheck->execute();
        } else {
          $Qcheck = $this->db->prepare('select count(r.reviews_id) as reviews_total, 
                                               max(r.reviews_rating) as max_reviews
                                      from :table_reviews r,
                                           :table_reviews_description rd
                                      where r.products_id = :products_id
                                      and rd.languages_id = :languages_id
                                      and r.reviews_id = rd.reviews_id
                                      and r.status = 1
                                      and r.customers_group_id > 0
                                      ');
          $Qcheck->bindInt(':products_id', $products_id);
          $Qcheck->bindInt(':languages_id', $this->lang->getId());
          $Qcheck->execute();
        }
      }

      if ($Qcheck->valueInt('reviews_total') > 0) {
        $max = $Qcheck->valueInt('max_reviews');
      } else {
        $max = 1;
      }

      return $max;
    }

    /**
     * @param int $products_id
     * @return string
     */
    public function getAuthor(int $products_id): string
    {
      $Qauthor = $this->db->prepare('select r.customers_name
                                    from :table_reviews r
                                    where r.products_id = :products_id
                                    and r.status = 1
                                    limit 1
                                   ');
      $Qauthor->bindInt(':products_id', $products_id);
      $Qauthor->execute();

      if (!empty($Qauthor->value('customers_name'))) {
        $author = '*** ' . HTML::outputProtected(substr($Qauthor->value('customers_name') . ' ', 4, -4)) . ' ***';
      } else {
        $author = '';
      }

      return $author;
    }

    /**
     * @param int $products_id
     * @return int
     */
    public function getCount(int $products_id): int
    {
      $Qcount = $this->db->prepare('select count(r.reviews_id) as reviews_total
                                from :table_reviews r
                                where r.products_id = :products_id
                                and r.status = 1
                               ');
      $Qcount->bindInt(':products_id', $products_id);
      $Qcount->execute();

      if ($Qcount->valueInt('reviews_total')) {
        $count = $Qcount->valueInt('reviews_total');
      } else {
        $count = 1;
      }

      return $count;
    }
  }