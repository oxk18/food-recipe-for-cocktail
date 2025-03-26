<?php
include_once('./_common.php');
if (!defined('_GNUBOARD_')) exit;

$g5['title'] = 'Food Recipe Search';
include_once(G5_PATH.'/head.php');
?>

        <div class="food-search-container">
        <form method="GET" class="food-search-form">
            <select name="search_type">
                <option value="name">음식 이름으로 검색 (e.g. pie)</option>
                <option value="area">인종으로 검색 (e.g. canadian)</option>
                <option value="ingredient">재료로 검색 (e.g. beef)</option>
                <option value="category">카테고리로 검색 (e.g. seafood)</option>
            </select>
            <input type="text" name="food_search" placeholder="Submit your search in english" 
                value="<?php echo isset($_GET['food_search']) ? htmlspecialchars($_GET['food_search']) : ''; ?>">
            <input type="submit" value="검색" class="btn_submit">
        </form>
    
        <?php
        if (isset($_GET['food_search']) && !empty($_GET['food_search'])) {
            $search_query = urlencode($_GET['food_search']);
            $search_type = isset($_GET['search_type']) ? $_GET['search_type'] : 'name';
            
            switch($search_type) {
                case 'area':
                    $api_url = "https://www.themealdb.com/api/json/v1/1/filter.php?a={$search_query}";
                    break;
                case 'category':
                    $api_url = "https://www.themealdb.com/api/json/v1/1/filter.php?c={$search_query}";
                    break;
                case 'ingredient':
                        $api_url = "https://www.themealdb.com/api/json/v1/1/filter.php?i={$search_query}";
                        break;    
                default:
                    $api_url = "https://www.themealdb.com/api/json/v1/1/search.php?s={$search_query}";
            }


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($data && isset($data['meals']) && is_array($data['meals'])) {
            echo '<div class="food-results">';
            foreach ($data['meals'] as $meal) {


                // For area, category and ingredient searches, we need to fetch full meal details
                if ($search_type === 'area' || $search_type === 'category' || $search_type === 'ingredient') {
                    $meal_id = $meal['idMeal'];
                    $detail_url = "https://www.themealdb.com/api/json/v1/1/lookup.php?i=" . $meal_id;
                    
                    $ch_detail = curl_init();
                    curl_setopt($ch_detail, CURLOPT_URL, $detail_url);
                    curl_setopt($ch_detail, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_detail, CURLOPT_SSL_VERIFYPEER, false);
                    $detail_response = curl_exec($ch_detail);
                    curl_close($ch_detail);
                    
                    $detail_data = json_decode($detail_response, true);
                    if ($detail_data && isset($detail_data['meals'][0])) {
                        $meal = $detail_data['meals'][0];
                    }
                }


                ?>
                <div class="food-item">
                    <div class="food-image">
                        <img src="<?php echo $meal['strMealThumb']; ?>" alt="<?php echo htmlspecialchars($meal['strMeal']); ?>">
                    </div>
                    <div class="food-info">
                        <h3><?php echo htmlspecialchars($meal['strMeal']); ?></h3>
                        <p><strong>카테고리:</strong> <?php echo htmlspecialchars($meal['strCategory']); ?></p>
                        <p><strong>국가:</strong> <?php echo htmlspecialchars($meal['strArea']); ?></p>
                        <p><strong>조리 방법:</strong><br><?php echo nl2br(htmlspecialchars($meal['strInstructions'])); ?></p>
                        
                        <div class="ingredients">
                            <strong>재료:</strong>
                            <ul>
                            <?php
                            for ($i = 1; $i <= 20; $i++) {
                                $ingredient = $meal["strIngredient{$i}"];
                                $measure = $meal["strMeasure{$i}"];
                                if ($ingredient && trim($ingredient) !== '') {
                                    echo "<li>" . htmlspecialchars($ingredient);
                                    if ($measure && trim($measure) !== '') {
                                        echo " - " . htmlspecialchars($measure);
                                    }
                                    echo "</li>";
                                }
                            }
                            ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <?php
            }
            echo '</div>';
        } else {
            echo '<p class="empty-result">검색 결과가 없습니다.</p>';
        }
    }
    ?>
</div>

<style>
.food-search-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;    
}
/*
.food-search-form {
    margin-bottom: 30px;
    text-align: center;
}

.food-search-form input[type="text"] {
    width: 300px;
    padding: 8px;
    margin-right: 10px;
}
*/


.food-search-form {
    margin: 40px auto;
    text-align: center;
    background: rgba(255, 255, 255, 0.1);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.18);
    max-width: 800px;
}

.food-search-form select {
    width: 300px;
    padding: 12px;
    /*margin-bottom: 15px;*/
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    background: white;
    cursor: pointer;
    transition: all 0.3s ease;
}

.food-search-form select:hover {
    border-color: #4a90e2;
}

.food-search-form select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

.food-search-form input[type="text"] {
    width: 300px;
    padding: 12px;
    margin: 0 10px;
    border: 2px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: all 0.3s ease;
}

.food-search-form input[type="text"]:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 0 3px rgba(74, 144, 226, 0.3);
}

.food-search-form .btn_submit {
    padding: 12px 30px;
    background: linear-gradient(45deg, #4a90e2, #63b3ed);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-weight: bold;
}

.food-search-form .btn_submit:hover {
    background: linear-gradient(45deg, #357abd, #4a90e2);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.food-search-form .btn_submit:active {
    transform: translateY(0);
}








.food-item {
    display: flex;    
    margin-bottom: 30px;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    color:black;
    background: #111;    
    position: relative;    
    z-index: 0;
    box-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;
}

.food-item:before {
  content: "";
  background: linear-gradient(
    45deg,
    #ff0000,
    #ff7300,
    #fffb00,
    #48ff00,
    #00ffd5,
    #002bff,
    #7a00ff,
    #ff00c8,
    #ff0000
  );
  position: absolute;
  top: -2px;
  left: -2px;
  background-size: 400%;
  z-index: -1;
  filter: blur(5px);
  -webkit-filter: blur(5px);
  width: calc(100% + 4px);
  height: calc(100% + 4px);
  animation: glowing-food-item 20s linear infinite;
  transition: opacity 0.3s ease-in-out;
  border-radius: 10px;
}

@keyframes glowing-food-item {
  0% {
    background-position: 0 0;
  }
  50% {
    background-position: 400% 0;
  }
  100% {
    background-position: 0 0;
  }
}

.food-item:after {
  z-index: -1;
  content: "";
  position: absolute;
  width: 100%;
  height: 100%;
  /*background: #222;*/
  background: white;
  left: 0;
  top: 0;
  border-radius: 10px;
}

.food-item:hover { transform: scale(1.05); -webkit-transform: scale(1.05);}


.food-image {
    flex: 0 0 200px;
    margin-right: 20px;
}

.food-image img {
    width: 100%;
    border-radius: 5px;
}

.food-info {
    flex: 1;
}

.food-info h3 {
    margin-top: 0;
    color: #333;
}

.ingredients ul {
    list-style: none;
    padding-left: 0;
}

.ingredients li {
    margin-bottom: 5px;
    color: #666;
}

.empty-result {
    text-align: center;
    color: #666;
    padding: 20px;
}
/* 모바일 반응형 스타일 추가 */
@media screen and (max-width: 768px) {
    .food-item {
        flex-direction: column;
    }
    
    .food-image {
        flex: none;
        width: 100%;
        margin-right: 0;
        margin-bottom: 20px;
    }
    
    .food-info {
        width: 100%;
    }
/*
    .food-search-form input[type="text"] {
        width: 100%;
        margin-bottom: 10px;
    }
*/
    .food-search-form {
        padding: 20px;
    }

    .food-search-form select,
    .food-search-form input[type="text"] {
        width: 100%;
        margin: 10px 0;
    }

    .food-search-form .btn_submit {
        width: 100%;
        margin-top: 10px;
    }



}
</style>

<?php
include_once(G5_PATH.'/tail.php');
?>