<template>
  <div class="hello text-left">
    <div class="row mb-10">
      <div class="col">
        <h2 class="pb-3">{{ msg }}</h2>
      </div>
    </div>
    <form  @submit="checkForm"  id="playForm">
      <div v-if="error_key" class="alert alert-primary" role="alert">  
        <ul class="m-0 list-group list-group-flush">
          <li class="list-group-item list-group-item-primary" v-for="error in errors" v-bind:key="error.key_id">{{ error.type }}</li>
        </ul>
      </div>
      <div class="form-group row">
          <label for="inputUsername" class="col-sm-2 col-form-label">Username</label>
          <div class="col-sm-10">
              <input type="Username" class="form-control" v-model="inputUsername" id="inputUsername" placeholder="Username">
          </div>
      </div>
      <div class="form-group row">
          <label for="inputCards" class="col-sm-2 col-form-label">Player’s hand</label>
          <div class="col-sm-10">
              <input type="Cards" v-model="inputCards" class="form-control" id="inputCards" placeholder="Cards">
          </div>
      </div>
      <div v-if="isAllCardValid" class="form-group row">
          <label for="inputCards" class="col-sm-2 col-form-label">Generated Hand</label>
          <div class="col-sm-10">
              <p class="m-1">{{generateCards}}</p>
          </div>
      </div>
      <div class="form-group row text-center">
          <div class="col-sm-10 offset-sm-2 text-left">
              <button type="submit" class="btn btn-outline-primary ">Play</button>
          </div>
      </div>
      <div v-if="isAllCardValid"> 
        <hr>
        <h2>Game Result</h2>
        <div class="form-group row">
            <label for="inputCards" class="col-sm-2 col-form-label">Player : </label>
            <div class="col-sm-10">
                <p class="m-1">{{ playerWin }}</p>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputCards" class="col-sm-2 col-form-label">Generated : </label>
            <div class="col-sm-10">
                <p class="m-1">{{ generateCardWin }}</p>
            </div>
        </div>
        <div class="form-group row">
            <label for="inputCards" class="col-sm-2 col-form-label">Result : </label>
            <div class="col-sm-10">
                <p class="m-1" v-if="playerWin>generateCardWin">{{inputUsername}} win this game</p>
                <p class="m-1" v-if="playerWin<generateCardWin">{{inputUsername}} lose this game</p>
                <p class="m-1" v-if="playerWin==generateCardWin">Game is draw</p>
            </div>
        </div>
      </div>
    </form>
    <hr>
    <div v-if="players.data && players.data.length>0">
      <h2>Leader Board</h2>
      <br>
      <div v-if="error_api" class="alert alert-primary" role="alert">  
          <ul class="m-0 list-group list-group-flush">
            <li class="list-group-item list-group-item-primary" v-for="(key, value) in error_api" v-bind:key="key">{{ error_api[value][0] }}</li>
          </ul>
      </div>
      <hr>
      <div class="row">
        <div class="col">Player's Name</div>
        <div class="col">Played Game</div>
        <div class="col">Won Game</div>
      </div>
      <hr>
      <div class="row" v-for="player in players.data" v-bind:key="player.id">
        <div class="col">{{player.userName}}</div>
        <div class="col">{{player.playedGame}}</div>
        <div class="col">{{player.wonGame}}</div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'HelloWorld',
  props: {
    msg: String,
  },
  created: function () {
    this. fetchLeaderboard();
  },
  data(){
    return {
      error_key:0,
      error_api:0,
      errors:[],
      inputUsername:null,
      inputCards:null,
      validCards:["2","3","4","5","6","7","8","9","10","J","Q","K","A"],
      userCardsArray:[],
      players:[],
      inputCardsForFilter:[],
      generateCards :'',
      generateCardsArray : [],
      playerWin:0,
      generateCardWin:0,
      isAllCardValid:false,
      leaderboard : []
    }
  },
  methods: {
      checkForm: function (e) {
        e.preventDefault();
        this.error_key = this.playerWin = this.generateCardWin = 0;
        this.isAllCardValid = false;
        this.errors = [];

        if (!this.inputUsername) {
          this.generateError("User Name is required.");
        }
        if (!this.inputCards) {
          this.generateError("Cards are required.");
        } else {
          this.inputCardsForFilter = this.inputCards.trim().toUpperCase().split(" ");
          var onlyValidCards = this.inputCardsForFilter.filter(this.validCard);
          if(this.inputCardsForFilter.length != onlyValidCards.length)
            return false;
          console.log('onlyValidCards',onlyValidCards);
        }

        if (!this.error_key) {
          this.isAllCardValid = true;
          this.generateCardsArray = this.inputCardsForFilter.map(this.generateCard);
          this.generateCards = this.generateCardsArray.join(' ');
          console.log('generateCardsArray',this.generateCardsArray);
          this.strorResult();
        }
        return true;
      },
      generateError: function (errortype) {
        this.errors.push({key_id :this.generateKey(),type : errortype});
      },
      generateKey: function () {
        return this.error_key+=1;
      },
      generateCard: function (playerCard) {
        let generateCard = this.validCards[Math.floor(Math.random()*this.validCards.length)];
        let playerCardIndex = this.validCards.indexOf(playerCard.toUpperCase());
        let generateCardIndex = this.validCards.indexOf(generateCard);
        if(playerCardIndex > generateCardIndex){
          this.playerWin++;
        }
        if(playerCardIndex < generateCardIndex){
          this.generateCardWin++;
        }
        console.log(playerCard,generateCard,this.validCards.indexOf(playerCard.toUpperCase()),this.validCards.indexOf(generateCard),this.playerWin,this.generateCardWin)
        return generateCard;
      },
      validCard: function (card) {
        if(this.validCards.indexOf(card) == -1)
        {  
          this.generateError("Card is invalid : "+card);
          return false;
        }
        return true;
      },
      fetchLeaderboard() {
        const options = {
          url: 'http://localhost:8888/leaderboard',
          method: 'GET',
        }
        this.$axios(options).then((res) => {console.log('Fetching suceeded!'+res); this.players = res.data; console.log(this.players)})
            .catch((err) => {console.error('Fetching failed.'+err); this.error_api=err.response.data;});
      },
      strorResult() {
        const options = {
          url: 'http://localhost:8888/store-playcard-result',
          method: 'POST',
          data: {
            inputCards: this.inputCardsForFilter,
            user_name: this.inputUsername,
            user_score: this.playerWin,
            generated_hand_score: this.generateCardWin,
            user_win: ((this.playerWin>this.generateCardWin)?1:0),
          }
        }
        this.$axios(options).then((res) => {console.log('store suceeded!'+res); this.fetchLeaderboard();})
            .catch((err) => {console.error('store failed.'+err);this.error_api=err.response.data;});
        return true;
      }
  }
}
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped>
</style>
