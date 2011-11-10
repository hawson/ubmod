<?php
/*
 * The contents of this file are subject to the University at Buffalo Public
 * License Version 1.0 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.ccr.buffalo.edu/licenses/ubpl.txt
 *
 * Software distributed under the License is distributed on an "AS IS" basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 *
 * The Original Code is UBMoD.
 *
 * The Initial Developer of the Original Code is Research Foundation of State
 * University of New York, on behalf of University at Buffalo.
 *
 * Portions created by the Initial Developer are Copyright (C) 2007 Research
 * Foundation of State University of New York, on behalf of University at
 * Buffalo.  All Rights Reserved.
 *
 * Alternatively, the contents of this file may be used under the terms of
 * either the GNU General Public License Version 2 (the "GPL"), or the GNU
 * Lesser General Public License Version 2.1 (the "LGPL"), in which case the
 * provisions of the GPL or the LGPL are applicable instead of those above. If
 * you wish to allow use of your version of this file only under the terms of
 * either the GPL or the LGPL, and not to allow others to use your version of
 * this file under the terms of the UBPL, indicate your decision by deleting
 * the provisions above and replace them with the notice and other provisions
 * required by the GPL or the LGPL. If you do not delete the provisions above,
 * a recipient may use your version of this file under the terms of any one of
 * the UBPL, the GPL or the LGPL.
 */

/**
 * Tag model.
 *
 * @author Jeffrey T. Palmer <jtpalmer@ccr.buffalo.edu>
 * @version $Id$
 * @copyright Center for Computational Research, University at Buffalo, 2011
 * @package Ubmod
 */

/**
 * Tag model.
 *
 * @package Ubmod
 */
class Ubmod_Model_Tag
{

  /**
   * Returns all the tags in the database.
   *
   * @return array All the tag names.
   */
  public static function getAll()
  {
    $sql = "
      SELECT DISTINCT tags
      FROM dim_user
      WHERE tags IS NOT NULL
    ";

    $dbh = Ubmod_DbService::dbh();
    $stmt = $dbh->prepare($sql);
    $r = $stmt->execute();
    if (!$r) {
      $err = $stmt->errorInfo();
      throw new Exception($err[2]);
    }

    $tags = array();

    while ($row = $stmt->fetch()) {
      $tags = array_merge($tags, json_decode($row['tags'], 1));
    }

    $tags = array_unique($tags);

    natcasesort($tags);

    return array_values($tags);
  }

  /**
   * Returns all the tag keys in the database.
   *
   * @return array
   */
  public static function getKeys()
  {
    $tags = self::getAll();

    $keys = array();
    foreach ($tags as $tag) {
      list($key) = self::splitTag($tag);
      $keys[] = $key;
    }

    return array_unique($keys);
  }

  /**
   * Returns all the tag values associated with a tag key.
   *
   * @param string $key A tag key.
   *
   * @return array
   */
  public static function getValuesForKey($key)
  {
    $tags = self::getAll();

    $values = array();
    foreach ($tags as $tag) {
      list($tagKey, $value) = self::splitTag($tag);
      if ($tagKey === $key) {
        $values[] = $value;
      }
    }

    return array_unique($values);
  }

  /**
   * Returns all the tags in the database that match the given string.
   *
   * Matching is case-insensitive. A tag is considered a match if the
   * given string prefix of the tag.
   *
   * @param string $query The string to match against.
   *
   * @return array All the tag names that match.
   */
  public static function getMatching($query)
  {
    // Use lowercase for case-insensitive matching
    $query = strtolower($query);

    // Substring length
    $length = strlen($query);

    $tags = array();

    foreach (self::getAll() as $tag) {
      if (strtolower(substr($tag, 0, $length)) === $query) {
        $tags[] = $tag;
      }
    }

    return $tags;
  }

  /**
   * Returns all the tag keys in the database that match the given
   * string.
   *
   * Matching is case-insensitive. A tag key is considered a match if
   * the given string prefix of the tag key.
   *
   * @param string $query The string to match against.
   *
   * @return array All the tag keys that match.
   */
  public static function getKeysMatching($query)
  {
    // Use lowercase for case-insensitive matching
    $query = strtolower($query);

    // Substring length
    $length = strlen($query);

    $keys = array();

    foreach (self::getKeys() as $key) {
      if (strtolower(substr($key, 0, $length)) === $query) {
        $keys[] = $key;
      }
    }

    return $keys;
  }

  /**
   * Returns an array of tags that have activity for the given
   * parameters.
   *
   * @param Ubmod_Model_QueryParams $params The query parameters.
   *
   * @return array
   */
  public static function getTagsWithActivity(Ubmod_Model_QueryParams $params)
  {
    $qb = new Ubmod_DataWarehouse_QueryBuilder();
    $qb->setFactTable('fact_job');
    $qb->addDimensionTable('dim_user');
    $qb->addSelectExpressions(array(
      'tags' => "COALESCE(dim_user.tags, '[]')",
    ));
    $qb->setQueryParams($params);
    $qb->setGroupBy('tags');
    $qb->clearLimit();
    list($sql, $dbParams) = $qb->buildQuery();

    $dbh = Ubmod_DbService::dbh();
    $stmt = $dbh->prepare($sql);
    $r = $stmt->execute($dbParams);
    if (!$r) {
      $err = $stmt->errorInfo();
      throw new Exception($err[2]);
    }

    $tags = array();

    while ($row = $stmt->fetch()) {
      $tags = array_merge($tags, json_decode($row['tags'], 1));
    }

    $tags = array_unique($tags);

    // Filter tags by keyword
    if ($params->hasFilter()) {
      $filter = $params->getFilter();

      $filtered = array();

      foreach ($tags as $tag) {
        if (strpos($tag, $filter) !== false) {
          $filtered[] = $tag;
        }
      }

      $tags = $filtered;
    }

    // Filter tags by tag key
    if ($params->hasTagKey()) {
      $key = $params->getTagKey();

      $filtered = array();

      foreach ($tags as $tag) {
        list($tagKey) = self::splitTag($tag);

        if ($tagKey === $key) {
          $filtered[] = $tag;
        }
      }

      $tags = $filtered;
    }

    return $tags;
  }

  /**
   * Returns the number of tags that have activity for the given
   * parameters.
   *
   * @param Ubmod_Model_QueryParams $params The query parameters.
   *
   * @return int
   */
  public static function getActivityCount(Ubmod_Model_QueryParams $params)
  {
    return count(self::getTagsWithActivity($params));
  }

  /**
   * Returns activity data for all tags that have activity for the given
   * parameters.
   *
   * @param Ubmod_Model_QueryParams $params The query parameters.
   *
   * @return array
   */
  public static function getActivityList(Ubmod_Model_QueryParams $params)
  {
    $activity = array();

    // Copy the params since they are changed by this method.
    $tagParams = clone $params;

    // It isn't possible to GROUP BY a tag, so a separate query is
    // performed for each tag.
    foreach (self::getTagsWithActivity($tagParams) as $tag) {
      $tagParams->setTag($tag);
      $tagActivity = Ubmod_Model_Job::getActivity($tagParams);

      $tagActivity['tag'] = $tag;

      list($key, $value) = self::splitTag($tag);
      $tagActivity['tag_key']   = $key;
      $tagActivity['tag_value'] = $value;

      $activity[] = $tagActivity;
    }

    $sortFields
      = array('tag', 'jobs', 'avg_cpus', 'avg_wait', 'wallt', 'avg_mem');

    if ($tagParams->hasOrderByColumn()) {
      $column = $tagParams->getOrderByColumn();

      if (!in_array($column, $sortFields)) {
        $column  = 'wallt';
      }
      $dir = $tagParams->isOrderByDescending() ? 'DESC' : 'ASC';

      usort($activity, function($a, $b) use($column, $dir) {
        if ($column === 'tag') {
          if ($dir === 'ASC') {
            return strcasecmp($a[$column], $b[$column]);
          } else {
            return strcasecmp($b[$column], $a[$column]);
          }
        } else {
          if ($dir === 'ASC') {
            return $a[$column] > $b[$column];
          } else {
            return $b[$column] > $a[$column];
          }
        }
      });
    }

    if ($tagParams->hasLimitRowCount() && $tagParams->hasLimitOffset()) {
      $activity = array_slice($activity, $tagParams->getLimitOffset(),
        $tagParams->getLimitRowCount());
    }

    return $activity;
  }

  /**
   * Normalize a tag.
   *
   * @param string $tag A tag.
   *
   * @return string
   */
  public static function normalize($tag)
  {
    return trim($tag);
  }

  /**
   * Split a tag into a key value pair.
   *
   * @param string $tag The tag to split.
   *
   * @return array Array containing the key and value.
   */
  public static function splitTag($tag)
  {
      return explode('=', $tag, 2);
  }
}
