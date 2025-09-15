"""
NIRA System - Biometric Service
Somalia National Identification & Registration Authority
Python Flask service for facial recognition and biometric processing
"""

import os
import cv2
import numpy as np
import face_recognition
import pickle
import base64
from flask import Flask, request, jsonify
from werkzeug.utils import secure_filename
import logging

# Configure logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = Flask(__name__)

# Configuration
UPLOAD_FOLDER = 'uploads'
ALLOWED_EXTENSIONS = {'png', 'jpg', 'jpeg'}
MAX_FILE_SIZE = 5 * 1024 * 1024  # 5MB

# Create upload directory
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

app.config['UPLOAD_FOLDER'] = UPLOAD_FOLDER
app.config['MAX_CONTENT_LENGTH'] = MAX_FILE_SIZE

def allowed_file(filename):
    """Check if file extension is allowed"""
    return '.' in filename and \
           filename.rsplit('.', 1)[1].lower() in ALLOWED_EXTENSIONS

def encode_face(image_path):
    """Encode face from image file"""
    try:
        # Load image
        image = face_recognition.load_image_file(image_path)
        
        # Find face locations
        face_locations = face_recognition.face_locations(image)
        
        if not face_locations:
            return None, "No face detected in the image"
        
        if len(face_locations) > 1:
            return None, "Multiple faces detected. Please provide an image with only one face"
        
        # Get face encodings
        face_encodings = face_recognition.face_encodings(image, face_locations)
        
        if not face_encodings:
            return None, "Could not encode face"
        
        # Convert to base64 string for storage
        encoding_bytes = pickle.dumps(face_encodings[0])
        encoding_b64 = base64.b64encode(encoding_bytes).decode('utf-8')
        
        return encoding_b64, None
        
    except Exception as e:
        logger.error(f"Error encoding face: {str(e)}")
        return None, f"Error processing image: {str(e)}"

def compare_faces(encoding1_b64, encoding2_b64, tolerance=0.6):
    """Compare two face encodings"""
    try:
        # Decode base64 encodings
        encoding1_bytes = base64.b64decode(encoding1_b64)
        encoding2_bytes = base64.b64decode(encoding2_b64)
        
        encoding1 = pickle.loads(encoding1_bytes)
        encoding2 = pickle.loads(encoding2_bytes)
        
        # Compare faces
        distance = face_recognition.face_distance([encoding1], encoding2)[0]
        is_match = distance <= tolerance
        
        return {
            'is_match': is_match,
            'distance': float(distance),
            'confidence': float(1 - distance) * 100
        }
        
    except Exception as e:
        logger.error(f"Error comparing faces: {str(e)}")
        return None

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'service': 'NIRA Biometric Service',
        'version': '1.0.0'
    })

@app.route('/encode', methods=['POST'])
def encode_face_endpoint():
    """Encode face from uploaded image"""
    try:
        if 'image' not in request.files:
            return jsonify({
                'success': False,
                'message': 'No image file provided'
            }), 400
        
        file = request.files['image']
        
        if file.filename == '':
            return jsonify({
                'success': False,
                'message': 'No file selected'
            }), 400
        
        if not allowed_file(file.filename):
            return jsonify({
                'success': False,
                'message': 'Invalid file type. Only PNG, JPG, JPEG are allowed'
            }), 400
        
        # Save uploaded file
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)
        
        # Encode face
        encoding, error = encode_face(filepath)
        
        # Clean up uploaded file
        os.remove(filepath)
        
        if error:
            return jsonify({
                'success': False,
                'message': error
            }), 400
        
        return jsonify({
            'success': True,
            'message': 'Face encoded successfully',
            'encoding': encoding
        })
        
    except Exception as e:
        logger.error(f"Error in encode endpoint: {str(e)}")
        return jsonify({
            'success': False,
            'message': 'Internal server error'
        }), 500

@app.route('/compare', methods=['POST'])
def compare_faces_endpoint():
    """Compare two face encodings"""
    try:
        data = request.get_json()
        
        if not data or 'encoding1' not in data or 'encoding2' not in data:
            return jsonify({
                'success': False,
                'message': 'Both encodings are required'
            }), 400
        
        encoding1 = data['encoding1']
        encoding2 = data['encoding2']
        tolerance = data.get('tolerance', 0.6)
        
        result = compare_faces(encoding1, encoding2, tolerance)
        
        if result is None:
            return jsonify({
                'success': False,
                'message': 'Error comparing faces'
            }), 500
        
        return jsonify({
            'success': True,
            'result': result
        })
        
    except Exception as e:
        logger.error(f"Error in compare endpoint: {str(e)}")
        return jsonify({
            'success': False,
            'message': 'Internal server error'
        }), 500

@app.route('/verify', methods=['POST'])
def verify_face_endpoint():
    """Verify face against stored encoding"""
    try:
        data = request.get_json()
        
        if not data or 'stored_encoding' not in data or 'image' not in data:
            return jsonify({
                'success': False,
                'message': 'Stored encoding and image are required'
            }), 400
        
        stored_encoding = data['stored_encoding']
        
        # Handle image data (base64 or file upload)
        if isinstance(data['image'], str):
            # Base64 encoded image
            image_data = base64.b64decode(data['image'])
            filename = 'temp_verify.jpg'
            filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
            
            with open(filepath, 'wb') as f:
                f.write(image_data)
        else:
            return jsonify({
                'success': False,
                'message': 'Invalid image format'
            }), 400
        
        # Encode the new face
        new_encoding, error = encode_face(filepath)
        
        # Clean up temp file
        if os.path.exists(filepath):
            os.remove(filepath)
        
        if error:
            return jsonify({
                'success': False,
                'message': error
            }), 400
        
        # Compare faces
        result = compare_faces(stored_encoding, new_encoding)
        
        if result is None:
            return jsonify({
                'success': False,
                'message': 'Error comparing faces'
            }), 500
        
        return jsonify({
            'success': True,
            'verified': result['is_match'],
            'confidence': result['confidence'],
            'distance': result['distance']
        })
        
    except Exception as e:
        logger.error(f"Error in verify endpoint: {str(e)}")
        return jsonify({
            'success': False,
            'message': 'Internal server error'
        }), 500

@app.route('/detect', methods=['POST'])
def detect_faces_endpoint():
    """Detect faces in uploaded image"""
    try:
        if 'image' not in request.files:
            return jsonify({
                'success': False,
                'message': 'No image file provided'
            }), 400
        
        file = request.files['image']
        
        if file.filename == '':
            return jsonify({
                'success': False,
                'message': 'No file selected'
            }), 400
        
        if not allowed_file(file.filename):
            return jsonify({
                'success': False,
                'message': 'Invalid file type. Only PNG, JPG, JPEG are allowed'
            }), 400
        
        # Save uploaded file
        filename = secure_filename(file.filename)
        filepath = os.path.join(app.config['UPLOAD_FOLDER'], filename)
        file.save(filepath)
        
        # Load image
        image = face_recognition.load_image_file(filepath)
        
        # Find face locations
        face_locations = face_recognition.face_locations(image)
        
        # Clean up uploaded file
        os.remove(filepath)
        
        return jsonify({
            'success': True,
            'face_count': len(face_locations),
            'faces_detected': len(face_locations) > 0,
            'face_locations': face_locations
        })
        
    except Exception as e:
        logger.error(f"Error in detect endpoint: {str(e)}")
        return jsonify({
            'success': False,
            'message': 'Internal server error'
        }), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
