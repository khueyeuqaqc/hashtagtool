var AccessToken = document.querySelector('#access-token'),
    postId = document.querySelector('#post-id'),
    commentRange = document.querySelector('#comment-range'),
    commentsArray = [];

//Lấy access token đã được lưu trong bộ nhớ
AccessToken.value = localStorage.getItem('AccessToken');

//Hàm lưu access token vào bộ nhớ
function saveToken() {
    localStorage.setItem('AccessToken', AccessToken.value);
    alert('Đã lưu Token')
}
//Hàm lấy comment từ Facebook
function getComment() {
    if (postId.value != '') {
        if (commentRange.value > 0) {
            fetch(`https://graph.facebook.com/${postId.value}/comments?fields=id,message,from{id,name,picture}&limit=5000&access_token=${AccessToken.value}`)
                .then(response => {
                    if (response.status === 400) {
                        //Nếu yêu cầu trả về báo lỗi 400 thì thông báo lỗi Token
                        alert('Lỗi Token!!!')
                    } else {
                        if (response.status === 404) {
                            //Nếu yêu cầu trả về báo lỗi 404 thì thông báo lỗi Token
                            alert('Lỗi id bài đăng!!!')

                        } else {
                            // Nếu không có lỗi thì trả về kết quả dưới dạng Json
                            return response.json()
                        }
                    }
                })
                .then(data => {
                    // Bắt dữ liệu và gọi đến hàm thêm comment vào mảng
                    addCommentToArray(data)
                })
                .catch(err => {})
        } else {
            alert('Số lượng comment không được nhỏ hơn 1!!!')
        }

    } else {
        alert('ID bài đăng không được để trống!!!')
    }
}
// Hàm thêm comment vào mảng
function addCommentToArray(a) {
    if (a.data) {
        //Nêu dữ liệu không trống thì thực hiện vòng lặp thêm dữ liệu vào mảng tạm
        for (let i in a.data) {
            if (a.data[i].id) {
                var temp = [];
                temp.push(a.data[i].id);
                temp.push(a.data[i].from.id);
                temp.push(a.data[i].from.name);
                temp.push(a.data[i].from.picture);
                temp.push(a.data[i].message);
                commentsArray.push(temp); //Lấy dữ liệu từ mảng tạm và đưa vào mảng comment chính
            }
        }
    }
    if (a && a.paging && a.paging.next) {
        //Nếu dữ liệu Json trả về có chứa "paging.next" thì tiến hành fetch tiếp để lấy thêm dữ liệu và thêm vào mảng
        fetch(a.paging.next)
            .then(e => e.json())
            .then(n => addCommentToArray(n));
    } else {
        //Nếu không sẽ gọi đến hàm lấy ra comment ngẫu nhiên trong mảng
        selectComment();
    }
}
//Hàm lấy ra comment ngẫu nhiên trong mảng theo số lượng comment chỉ định
function selectComment() {
    if (commentRange.value > commentsArray.length) {
        // Nếu số lượng comment nhập vào nhiều hơn số lượng comment lấy được 
        // thì số lượng comment nhập vào sẽ thay bằng số comment lấy được
        commentRange.value = commentsArray.length;
    }
    //Vòng lặp lấy ra các phần tử trong mảng
    for (var i = 0; i < commentRange.value; i++) {
        var index = Math.floor(Math.random() * commentsArray.length); //Lấy ví trí ngẫu nhiên trong mảng
        //Chèn dữ liệu trong mảng lấy được vào chuỗi "str"
        str = `
            <div class="comment">
                <figure class="avatar">
                    <img class="img-fluid"
                        src="${commentsArray[index][3]}"
                        alt="Avatar">
                </figure>
                <div class="comment-message">
                    <a href="https://facebook.com/${commentsArray[index][1]}" class="name" target="_blank">${commentsArray[index][2]}</a>
                    <p class="message">${commentsArray[index][4]}</p>
                    <a href="https://facebook.com/${commentsArray[index][0]}" target="_blank">Vào xem comment</a>
                </div>
            </div>`;
        //Thêm chuỗi "str" vào trong HTML
        document.getElementById('comment-result').innerHTML += str;
        //Thiết lập thuộc tính cho thẻ div có id là "comment-result" từ ẩn sang hiện
        document.getElementById('comment-result').style.display = 'block';
    }
}